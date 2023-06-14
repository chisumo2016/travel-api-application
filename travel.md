### Travel API
        uuid
    -  php artisan make:model Role -m
    -  php artisan make:migration create_role_user_table
             $table->foreignId('role_id')->references('id')->on('roles');
             $table->foreignId('role_id')->constrained(); //->references('id')->on('roles')
