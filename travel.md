### Travel API
        uuid
    -  php artisan make:model Role -m
    - Add the mass assigment
    -  php artisan make:migration create_role_user_table
             $table->foreignId('role_id')->references('id')->on('roles');
             $table->foreignId('role_id')->constrained(); //->references('id')->on('roles')
    - php artisan make:model Travel -m
    - Add the mass assigment

    Tinker
        str('my travell')->slug();
    - So make observer
        php artisan make:observer  TravelObserver --model=Travel
    - We to modify or data before its saves in database.
        public function creating(Travel $travel): void
            {
                $travel->slug = str($travel->name)->slug();
            }
    - Register our observer in events  EventServiceProvider
             protected  $observers = [
            Travel::class => [TravelObserver::class]
        ];

    public function boot(): void
    {
        Travel::observe(TravelObserver::class);
    }
    - The above is not recommendes
    - We can use the packagee for sluggable for unique behaviour
        https://github.com/cviebrock/eloquent-sluggable
                composer require cviebrock/eloquent-sluggable
       https://github.com/spatie/laravel-sluggable

    - Accessor (number of nights)
         public  function  numberOfNights(): Attribute
            {
                return  Attribute::make(
                    get: fn($value, $attributes) => $attributes['number_of_days'] - 1
                );
            }
            OLDER VERSION
             public  function  getNumberOfNightsAttribute()
                {
                    return $this->number_of_days - 1;
                }
        > App\Models\Travel::create(['name'=>'Some Thing','description'=> 'try something','number_of_days'=>5])
        = App\Models\Travel {#6219
        name: "Some Thing",
        description: "try something",
        number_of_days: 5,
        slug: "some-thing-3",
        updated_at: "2023-06-15 06:24:38",
        created_at: "2023-06-15 06:24:38",
        id: 3,
        }
        
        > $travel = Travel::latest()->first();
        [!] Aliasing 'Travel' to 'App\Models\Travel' for this Tinker session.
        = App\Models\Travel {#6231
        id: 3,
        is_public: 0,
        slug: "some-thing-3",
        name: "Some Thing",
        description: "try something",
        number_of_days: 5,
        created_at: "2023-06-15 06:24:38",
        updated_at: "2023-06-15 06:24:38",
        }
        
        > $travel->number_of_nights
