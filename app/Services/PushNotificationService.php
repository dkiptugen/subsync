<?php

namespace App\Services;

use http\Client\Request;

class PushNotificationService
    {
        private function save(Request $request)
            {
                $user = \App\User::find($request->user_id);

                $user->updatePushSubscription($request->endpoint, $request->key, $request->token, $request->contentEncoding);

            }

        public function delete(Request $request)
            {
                $user = \App\User::find($request->user_id);

                $user->deletePushSubscription($request->endpoint);

            }
    }
