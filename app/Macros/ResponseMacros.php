<?php

    namespace App\Macros;

    use Illuminate\Routing\ResponseFactory;

    class ResponseMacros
        {
            public static function register()
                {
                    ResponseFactory::macro('api', function (
                        $data = null,
                        $message = null,
                        $status = 200,
                        $meta = []
                    ) {

                            return response()->json([
                                                        'success' => $status < 400,
                                                        'status'  => $status,
                                                        'message' => $message,
                                                        'data'    => $data,
                                                        'meta'    => $meta,
                                                    ], $status);
                        });
                }
        }
