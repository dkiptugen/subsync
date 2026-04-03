<?php

    namespace App\Console\Commands\Utilities;

    use Illuminate\Routing\Console\ControllerMakeCommand;

    class MakeCustomController extends ControllerMakeCommand
        {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
            protected $name = 'make:controller-custom';

        /**
         * The console command description.
         *
         * @var string
         */
            protected $description = 'Create a new controller class with an additional custom method';

        /**
         * Build the class with the given name.
         *
         * @param  string  $name
         * @return string
         */
            protected function buildClass($name)
                {
                    $controllerClass = parent::buildClass($name);

                    $customMethod = $this->getCustomMethod();

                    // Find the position of the last closing brace
                    $position = strrpos($controllerClass, '}');

                    // Insert the custom method before the last closing brace
                    return substr_replace($controllerClass, "\n" . $customMethod . "\n}", $position, 1);
                }

        /**
         * Get the custom method that should be added to the controller.
         *
         * @return string
         */
            protected function getCustomMethod()
                {
                    return <<<EOD

            /**
             * Custom method added for datatable.
             *
             * @return \\Illuminate\\Http\\JsonResponse
             */
            public function datatable(Request \$request, Datatable \$datatable)
            {
                \$datatable->columns = [0=>'id'];
                return response()->json(\$datatable->data(\$request));
            }

        EOD;
                }
        }
