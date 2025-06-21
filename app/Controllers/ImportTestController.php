<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\DataForSeoImportService;

class ImportTestController extends Controller
{
    public function index()
    {
        return view('import_test');
    }
    
    /**
     * Импорт данных Chama Mama из JSON файла
     */
    public function importChamaMama()
    {
        try {
            // JSON данные Chama Mama из DataForSEO API
            $chamaMamaJson = '{
                "id": "06182034-3181-0544-0000-e852fc1ca37f",
                "status_code": 20000,
                "status_message": "Ok.",
                "time": "0.1246 sec.",
                "cost": 0.0106,
                "result_count": 1,
                "result": [
                    {
                        "total_count": 2,
                        "count": 2,
                        "offset": 0,
                        "items": [
                            {
                                "type": "business_listing",
                                "title": "Chama Mama",
                                "original_title": null,
                                "description": "Laid-back choice for Georgian staples like khachapuri paired with natural, organic wines.",
                                "category": "Georgian restaurant",
                                "category_ids": [
                                    "georgian_restaurant",
                                    "eastern_european_restaurant",
                                    "restaurant"
                                ],
                                "additional_categories": [
                                    "Eastern European restaurant",
                                    "Restaurant"
                                ],
                                "cid": "14075755502346280585",
                                "feature_id": "0x89c2598e3b7c88a7:0xc357210d86999e89",
                                "address": "149 W 14th St, New York, NY 10011",
                                "address_info": {
                                    "borough": "Manhattan",
                                    "address": "149 W 14th St",
                                    "city": "New York",
                                    "zip": "10011",
                                    "region": "New York",
                                    "country_code": "US"
                                },
                                "place_id": "ChIJp4h8O45ZwokRiZ6Zhg0hV8M",
                                "phone": "+1646-438-9007",
                                "url": "https://www.chamamama.com/location/chama-mama/?utm_source=gmb&utm_medium=organic&utm_campaign=local&utm_id=gmb-chelsea",
                                "domain": "www.chamamama.com",
                                "logo": "https://lh5.googleusercontent.com/-dDvysx5VlBw/AAAAAAAAAAI/AAAAAAAAAAA/OID_99uDykc/s44-p-k-no-ns-nd/photo.jpg",
                                "main_image": "https://lh3.googleusercontent.com/p/AF1QipOxfOz60n--4s4a2xuR4wq1oAGjcp8hvZVtF62a=w408-h263-k-no",
                                "total_photos": 3090,
                                "snippet": "149 W 14th St, New York, NY 10011",
                                "latitude": 40.7384413,
                                "longitude": -73.9988997,
                                "is_claimed": true,
                                "attributes": {
                                    "available_attributes": {
                                        "service_options": [
                                            "has_seating_outdoors",
                                            "has_curbside_pickup",
                                            "has_no_contact_delivery",
                                            "has_delivery",
                                            "has_takeout",
                                            "serves_dine_in"
                                        ],
                                        "accessibility": [
                                            "has_wheelchair_accessible_entrance",
                                            "has_wheelchair_accessible_restroom",
                                            "has_wheelchair_accessible_seating"
                                        ],
                                        "offerings": [
                                            "serves_alcohol",
                                            "serves_beer",
                                            "serves_cocktails",
                                            "serves_coffee",
                                            "serves_comfort_food",
                                            "serves_halal_food",
                                            "serves_liquor",
                                            "serves_organic",
                                            "serves_small_plates",
                                            "serves_vegan",
                                            "serves_vegetarian",
                                            "serves_wine"
                                        ],
                                        "dining_options": [
                                            "serves_brunch",
                                            "serves_lunch",
                                            "serves_dinner",
                                            "has_catering",
                                            "serves_dessert",
                                            "has_seating"
                                        ],
                                        "atmosphere": [
                                            "feels_casual",
                                            "feels_cozy",
                                            "feels_hip"
                                        ],
                                        "crowd": [
                                            "welcomes_families",
                                            "suitable_for_groups",
                                            "welcomes_lgbtq",
                                            "popular_with_tourists"
                                        ],
                                        "planning": [
                                            "accepts_reservations"
                                        ],
                                        "payments": [
                                            "pay_credit_card",
                                            "pay_debit_card",
                                            "pay_mobile_nfc"
                                        ],
                                        "children": [
                                            "welcomes_children",
                                            "has_changing_tables",
                                            "has_high_chairs"
                                        ],
                                        "pets": [
                                            "welcomes_dogs",
                                            "allows_dogs_inside",
                                            "allows_dogs_outside"
                                        ]
                                    }
                                },
                                "place_topics": {
                                    "brunch": 655,
                                    "khachapuri": 158,
                                    "cocktails": 95
                                },
                                "rating": {
                                    "rating_type": "Max5",
                                    "value": 4.6,
                                    "votes_count": 2342,
                                    "rating_max": null
                                },
                                "price_level": "inexpensive",
                                "rating_distribution": {
                                    "1": 66,
                                    "2": 52,
                                    "3": 121,
                                    "4": 318,
                                    "5": 1785
                                },
                                "people_also_search": [
                                    {
                                        "cid": "3993258332057882379",
                                        "title": "SAPERAVI",
                                        "rating": {
                                            "rating_type": "Max5",
                                            "value": 4.9,
                                            "votes_count": 1053
                                        }
                                    },
                                    {
                                        "cid": "12932609383773010823",
                                        "title": "Aragvi",
                                        "rating": {
                                            "rating_type": "Max5",
                                            "value": 4.9,
                                            "votes_count": 2377
                                        }
                                    }
                                ],
                                "work_time": {
                                    "work_hours": {
                                        "timetable": {
                                            "sunday": [
                                                {
                                                    "open": {"hour": 10, "minute": 0},
                                                    "close": {"hour": 22, "minute": 0}
                                                }
                                            ],
                                            "monday": [
                                                {
                                                    "open": {"hour": 12, "minute": 0},
                                                    "close": {"hour": 22, "minute": 0}
                                                }
                                            ],
                                            "tuesday": [
                                                {
                                                    "open": {"hour": 12, "minute": 0},
                                                    "close": {"hour": 22, "minute": 0}
                                                }
                                            ],
                                            "wednesday": [
                                                {
                                                    "open": {"hour": 12, "minute": 0},
                                                    "close": {"hour": 22, "minute": 0}
                                                }
                                            ],
                                            "thursday": [
                                                {
                                                    "open": {"hour": 12, "minute": 0},
                                                    "close": {"hour": 22, "minute": 0}
                                                }
                                            ],
                                            "friday": [
                                                {
                                                    "open": {"hour": 12, "minute": 0},
                                                    "close": {"hour": 23, "minute": 0}
                                                }
                                            ],
                                            "saturday": [
                                                {
                                                    "open": {"hour": 10, "minute": 0},
                                                    "close": {"hour": 23, "minute": 0}
                                                }
                                            ]
                                        },
                                        "current_status": "open"
                                    }
                                },
                                "local_business_links": [
                                    {
                                        "type": "reservation",
                                        "title": "bit.ly",
                                        "url": "https://bit.ly/3duW1p4"
                                    },
                                    {
                                        "type": "menu",
                                        "title": "chamamama.com",
                                        "url": "https://www.chamamama.com/menus/"
                                    }
                                ],
                                "check_url": "https://www.google.com/maps?cid=14075755502346280585&hl=en&gl=US",
                                "last_updated_time": "2025-06-05 03:50:38 +00:00",
                                "first_seen": "2024-07-27 23:29:22 +00:00"
                            }
                        ]
                    }
                ]
            }';
            
            // Выполняем импорт
            $importService = new DataForSeoImportService();
            $result = $importService->importChamaMamaData($chamaMamaJson);
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Import error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Просмотр импортированных данных
     */
    public function viewImported()
    {
        $importService = new DataForSeoImportService();
        
        // Ищем Chama Mama
        $restaurants = $importService->searchRestaurants([
            'search' => 'Chama Mama',
            'limit' => 5
        ]);
        
        $detailedRestaurants = [];
        foreach ($restaurants as $restaurant) {
            $details = $importService->getRestaurantDetails($restaurant['id']);
            $detailedRestaurants[] = $details;
        }
        
        return $this->response->setJSON([
            'success' => true,
            'restaurants' => $detailedRestaurants
        ]);
    }
    
    /**
     * Тест поиска по атрибутам
     */
    public function testAttributeSearch()
    {
        $importService = new DataForSeoImportService();
        
        // Поиск ресторанов с доставкой
        $withDelivery = $importService->searchRestaurants([
            'attributes' => ['has_delivery'],
            'limit' => 10
        ]);
        
        // Поиск веганских ресторанов
        $vegan = $importService->searchRestaurants([
            'attributes' => ['serves_vegan'],
            'limit' => 10
        ]);
        
        // Поиск семейных ресторанов
        $familyFriendly = $importService->searchRestaurants([
            'attributes' => ['welcomes_families', 'welcomes_children'],
            'limit' => 10
        ]);
        
        return $this->response->setJSON([
            'success' => true,
            'searches' => [
                'with_delivery' => count($withDelivery),
                'vegan_options' => count($vegan),
                'family_friendly' => count($familyFriendly)
            ],
            'examples' => [
                'delivery_restaurants' => $withDelivery,
                'vegan_restaurants' => $vegan,
                'family_restaurants' => $familyFriendly
            ]
        ]);
    }
    
    /**
     * Статистика по атрибутам
     */
    public function attributeStats()
    {
        $importService = new DataForSeoImportService();
        $stats = $importService->getAttributeStats();
        
        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }
}