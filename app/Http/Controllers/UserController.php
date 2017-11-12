<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Hash;
use Stripe\Stripe;
use Mail;

class UserController extends Controller {

    function canceled() {
        $user = \Auth::user();
        // return view
        return view('users.canceled', [
            'user' => $user,
        ]);
    }

    function stripe() {
        // check for valid Stripe IP
        $stripe_ips = json_decode(file_get_contents("https://stripe.com/files/ips/ips_webhooks.json"));
        if (!in_array($_SERVER['REMOTE_ADDR'], $stripe_ips->WEBHOOKS)) return 'Not a valid Stripe Webhook';

        // valid IP? continue...
        $webhook = json_decode(file_get_contents("php://input"));

        // webhook types
        switch ($webhook->type) {
            // add user with specified app limits
            case 'customer.subscription.created':
                // don't do anything if this plan doesn't have metadata: Timers and Frenzies
                if (!isset($webhook->data->object->plan->metadata->Timers) || !isset($webhook->data->object->plan->metadata->Frenzies)) {
                    return;
                }
                // user exists
                if ($user = User::where('stripe_customer_id', '=', $webhook->data->object->customer)->first()) {
                    // update limits
                    $user->max_timers_count = $webhook->data->object->plan->metadata->Timers;
                    $user->max_frenzy_count = $webhook->data->object->plan->metadata->Frenzies;
                    $user->save();

                // user doesn't exist
                } else {
                    // get Customer from Stripe
                    \Stripe\Stripe::setApiKey(config('app.stripe_apikey'));
                    $customer = \Stripe\Customer::retrieve($webhook->data->object->customer);
                    if (isset($customer->email)) {
                        // add user
                        $password = str_random(12);
                        $user = new User();
                        $user->name = isset($customer->description) ? $customer->description : $customer->email;
                        $user->email = $customer->email;
                        $user->password = Hash::make($password);
                        $user->stripe_customer_id = $webhook->data->object->customer;
                        $user->max_timers_count = $webhook->data->object->plan->metadata->Timers;
                        $user->max_frenzy_count = $webhook->data->object->plan->metadata->Frenzies;
                        $user->save();

                        /**
                         * Send welcome email with password
                         */
                        Mail::send('emails.welcome', ['user' => $user, 'password' => $password], function ($m) use ($user) {
                            $m->to($user->email, $user->name)->subject(trans('translate.welcome'));
                        });
                    }
                }
                /**
                 * TODO: Cancel old subscriptions (with 'Timers' and 'Frenzies' metadata) via Stripe API.
                 */

                break;
            case 'customer.subscription.deleted':
                /**
                 * Billing canceled: Reduce limits to 0.
                 */
                if ($user = User::where('stripe_customer_id', $webhook->data->object->customer)->first()) {
                    $user->max_timers_count = 0;
                    $user->max_frenzy_count = 0;
                    $user->save();
                }
                break;
        }
        return 'ok';
    }
}
