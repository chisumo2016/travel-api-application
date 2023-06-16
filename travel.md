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

    - php artisan make:model Tour -m
    - Add the accessor 
    - Add the mass assigment
    - convert the id to uuid
        $table->uuid('id')->primary();
    - channge the foreign key to uuid as well
        $table->foreignId('travel_id')->constrained('travels');
                TO
         $table->foreignUuid('travel_id')->constrained('travels');

    - CHANGE in person_access_tokenn
        $table->morphs('tokenable'); //USER ID
        TO
        $table->uuidMorphs('tokenable'); //USER ID

    - Add the traits in all model associate with uuid
        use Illuminate\Database\Eloquent\Concerns\HasUuids;
        use HasUuids;
    - FRESH MIGRATION
        php artisan migrate:fresh 
    - DBDEAVER - ER DIAGRAM
        https://dbeaver.io/download/


## PART B  
    Public Endpoint for Travels with Tests

    - php artisan make:controller Api/V1/TravelController
    - Client doesnt want to know created_at oor updated_at 
    - To use API resources
        php artisan make:resource TravelResource
    -  call the resource  inside the TravelControllerr
    - As sooner on feeature is finishhed Test the applicaction PHP UNIT (AUTOMATED TEST) or PEST
        .  Delete the test inside the 
                tests/Feature/ExampleTest.php
                tests/Unit/ExampleTest.php
        . Let start create  test for Travel endpoint
            php artisan make:test TravelListTest     -----> Featured 
    - Setup the phpunit.xml
            <env name="DB_CONNECTION" value="sqlite"/>
           <env name="DB_DATABASE" value=":memory:"/>


    - Create Fatory for Model Travel
        php artisan make:factory TravelFactory --model=Travel
        php artisan test


    Public Endpoint for Tours with Tests 
        Route::get('travels/{travels}/tours', [TourController::class, 'index']);
        /api/v1/travels/[travels.id]/tours
        php artisan make:controller Api/V1/TourController
        php artisan make:resource TourResource

    - Test api 
            http://travel-api.test/api/v1/travels/some-thing/tours
            . Implemented Slug
                     public  function  getRouteKeyName(): string
                        {
                            return 'slug';
                        }
            . web routte will
                    Route::get('travels/{travels}/tours', [TourController::class, 'index']);
            . TourController
                return Tour::where('travel_id', $travels->id)->orderBy('starting_date')->get() ;
            .We have relationnship we caan use it
                     public  function  index(Travel $travel)
                        {
                              return $travel->tours()
                                  ->orderBy('starting_date')
                                  ->get() ;
                        }
    - Return some fields in resource
    - Test API

    - To write the PHP UNIT TEST
        . php artisan make:test TourListTest
        .Write all test
    - Create a TourFactory
        php artisan make:factory TourFactory --model=Tour
    - 
            public  function test_tours_list_returns_pagination():void
            {
                $toursPerPage = config('app.paginationPerPage.tours');
        
                $travel =   Travel::factory()->create();
                Tour::factory($toursPerPage +1 )->create(['travel__id' => $travel->id]);
        
                $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        
                $response->assertStatus(200);
                $response->assertJsonCount(1, 'data');
                $response->assertJsonPath('meta.current_page', 1);
            }
    - Old version

    - php artisan test --filter=TourListTest 


## Tours Filtering and Ordering
         http://travel-api.test/api/v1/travels/some-thing/tours?priceFrom=123&priceTo=456&dateFrom=2023-06-01&dateTo=2023-07-01
         http://travel-api.test/api/v1/travels/some-thing/tours?dateFrom=2023-06-19&dateTo=2023-07-01&priceFrom=99&priceTo=50&sortBy=price&sortOrder=asc

    - Open the  TourController
    - Form Validation class 
        php artisan make:request ToursListRequest
              $request->validate([
             'priceFrom'    => 'numeric',
             'priceTo'      => 'numeric',
             'dateFrom'     => 'date',
             'dateTo'       => 'date',
             'sortBy'       => Rule::in(['price']),
             'sortOrder'    => Rule::in(['asc' .'desc']),
         ],[
             'sortBy' => "The 'sortBy' parameter accepts only 'price' value",
             'sortOrder' => "The 'sortOrder' parameter accepts only 'asc' pr 'desc' value",
         ]);

## Artisan Command to Create Users
    - Creeate User Command 
        php artisan make:command CreateUserCommand
    - Create RoleSeeder
        php artisan make:seeder RoleSeeder
    - Launch
        php artisan db:seed --class=RoleSeeder

## Admin Endpoint to create Travel
    - Create the controller
                php artisan make:controller Api/V1/Admin/TravelController
    - create the route group with prefix admin
            Route::prefix('admin')->group(function () {
    
            Route::put('travels/{travel}', [\App\Http\Controllers\Api\V1\Admin\TravelController::class, 'update']);
        });
    - Travel Request validation
        php artisan make:request TravelRequest
    - TEST API
        POST:  http://travel-api.test/api/v1/admin/travels 
    - add two middlware auth and role admin
        ->middleware(['auth:sanctum']) will be powwered api token
    - Generate the LoginController
                php artisan make:controller Api/V1/Auth/LoginController --invokable
                
    - Add the route api 
        Route::post('login', LoginController::class);
    - Generat e the form request class
        php artisan make:request LoginRequest

    - Create Role Middle
        php artisan make:middleware RoleMiddlware
    - Register the middleware in kernel 
    - Testing the applicaation
    - Create PHP UNIT Test
        php artisan make:test LoginTest
        php artisan make:test AdminTest





















