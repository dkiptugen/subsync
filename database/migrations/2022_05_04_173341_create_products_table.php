<?php


    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
            {

                Schema::create('products', function (Blueprint $table)
                    {

                        $table->id();
                        $table->string('identifier')->unique();
                        $table->string('product_name');
                        $table->string('payment_methods');
                        $table->text('product_link');
                        $table->unsignedBigInteger('user_id');
                        $table->longText('payment_notification_link')->nullable();
                        $table->tinyInteger('status')->default(0);
                        $table->unsignedBigInteger('site_id');

                        $table->timestamps();
                        $table->foreign('user_id')
                              ->references('id')
                              ->on('users')
                              ->CascadeOnDelete()
                              ->CascadeOnUpdate();
                        $table->foreign('site_id')
                              ->references('id')
                              ->on('sites')
                              ->CascadeOnDelete()
                              ->CascadeOnUpdate();

                    });
            }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
            {

                Schema::dropIfExists('products');
            }
    };
