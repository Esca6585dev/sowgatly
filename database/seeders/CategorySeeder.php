<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use Faker\Factory as Faker;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $parentCategories = [
            [
                'name_tm' => 'Monobuketler',
                'name_en' => 'Mono Bouquets',
                'name_ru' => 'Монобукеты',
                'image' => 'category/category-seeder/mono.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Awtoryň buketleri',
                'name_en' => 'Florist\'s Specials',
                'name_ru' => 'Авторские букеты',
                'image' => 'category/category-seeder/author.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Gutydaky güller',
                'name_en' => 'Flowers in a Box',
                'name_ru' => 'Цветы в коробке',
                'image' => 'category/category-seeder/box.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Sebetdäki güller',
                'name_en' => 'Flowers in a Basket',
                'name_ru' => 'Цветы в корзине',
                'image' => 'category/category-seeder/basket.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Bölekleýin gül',
                'name_en' => 'By the Piece',
                'name_ru' => 'Цветы поштучно',
                'image' => 'category/category-seeder/apiece.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Guradylan güller',
                'name_en' => 'Dried Flowers',
                'name_ru' => 'Букеты из сухоцветов',
                'image' => 'category/category-seeder/dried_flowers.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Şarlar',
                'name_en' => 'Balloons',
                'name_ru' => 'Воздушные шары',
                'image' => 'category/category-seeder/ball.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Içki güller',
                'name_en' => 'Flowers for interior',
                'name_ru' => 'Цветы для интерьера',
                'image' => 'category/category-seeder/flowers_for_interior.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Gül sandyklary',
                'name_en' => 'Flower Crates',
                'name_ru' => 'Цветы в ящиках',
                'image' => 'category/category-seeder/crates.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Sowgat sebetleri we toplumlary',
                'name_en' => 'Gift Baskets & Sets',
                'name_ru' => 'Подарочные наборы',
                'image' => 'category/category-seeder/wedding.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Toý buketleri',
                'name_en' => 'Wedding Bouquets',
                'name_ru' => 'Букеты невесты',
                'image' => 'category/category-seeder/stabilized_flowers.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Goralýan güller',
                'name_en' => 'Preserved Flowers',
                'name_ru' => 'Стабилизированные цветы',
                'image' => 'category/category-seeder/stabilized_flowers.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Ýumşak oýunjaklar',
                'name_en' => 'Stuffed Toys',
                'name_ru' => 'Мягкие игрушки',
                'image' => 'category/category-seeder/toys.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Ösen gül',
                'name_en' => 'Advanced Floristry',
                'name_ru' => 'Композиции из цветов',
                'image' => 'category/category-seeder/floristic.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Sabyn buketleri',
                'name_en' => 'Soap Bouquets',
                'name_ru' => 'Букеты из мыла',
                'image' => 'category/category-seeder/soap_bouquets.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Gül aýy',
                'name_en' => 'Rose Bears',
                'name_ru' => 'Мишки из роз',
                'image' => 'category/category-seeder/bears_from_roses.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Emeli güller',
                'name_en' => 'Artificial Flowers',
                'name_ru' => 'Искусственные цветы',
                'image' => 'category/category-seeder/artificial_flowers_new.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Otkrytkalar',
                'name_en' => 'Postcards',
                'name_ru' => 'Открытки',
                'image' => 'category/category-seeder/postcards.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Jynaza gülleri',
                'name_en' => 'Funeral Flowers',
                'name_ru' => 'Траурные цветы',
                'image' => 'category/category-seeder/funeral_flowers.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Beýlekiler',
                'name_en' => 'More',
                'name_ru' => 'Другое',
                'image' => 'category/category-seeder/other_fancy_flowers.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Iýip bolýan buketler',
                'name_en' => 'Edible bouquets',
                'name_ru' => 'Съедобные букеты',
                'image' => 'category/category-seeder/1716301300_68852048.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Konditer önümleri we çörek önümleri',
                'name_en' => 'Confectionery & Bakery',
                'name_ru' => 'Кондитерские и пекарни',
                'image' => 'category/category-seeder/cake.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Janly ösümlikler',
                'name_en' => 'Live Plants',
                'name_ru' => 'Живые растения',
                'image' => 'category/category-seeder/plants.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Makiýaž up we duhi',
                'name_en' => 'Make-up & Perfume',
                'name_ru' => 'Косметика и парфюмерия',
                'image' => 'category/category-seeder/cosmetics_perfumery.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Çaý we kofe dükanlary',
                'name_en' => 'Tea & Coffee Shops',
                'name_ru' => 'Магазины чая и кофе',
                'image' => 'category/category-seeder/tea_coffee_shops.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Şaý-sepler',
                'name_en' => 'Jewellery',
                'name_ru' => 'Украшения',
                'image' => 'category/category-seeder/jewelry.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Sowgat toplumlary',
                'name_en' => 'Gift sets',
                'name_ru' => 'Подарочные наборы',
                'image' => 'category/category-seeder/1717668021_60453164.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Iýmit we içgiler',
                'name_en' => 'Food & Drinks',
                'name_ru' => 'Продукты и напитки',
                'image' => 'category/category-seeder/products.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Bezeg',
                'name_en' => 'Decor',
                'name_ru' => 'Декор',
                'image' => 'category/category-seeder/decor.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Tabak gap-gaçlary',
                'name_en' => 'Tableware',
                'name_ru' => 'Посуда',
                'image' => 'category/category-seeder/dishes.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Aksesuarlar',
                'name_en' => 'Accessories',
                'name_ru' => 'Аксессуары',
                'image' => 'category/category-seeder/accessories.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Egin-eşik',
                'name_en' => 'Clothing',
                'name_ru' => 'Одежда',
                'image' => 'category/category-seeder/1655372164_77782005.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Aýakgaplar',
                'name_en' => 'Shoes',
                'name_ru' => 'Обувь',
                'image' => 'category/category-seeder/1655375951_84649534.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'El bilen ýasalan we güýmenje',
                'name_en' => 'Handmade & Hobby',
                'name_ru' => 'Хендмейд и хобби',
                'image' => 'category/category-seeder/hobbies_creativity.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Çagalaryň eşikleri',
                'name_en' => 'Kids\' Clothing',
                'name_ru' => 'Одежда для детей',
                'image' => 'category/category-seeder/1655380759_38128557.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Baýramçylyk harytlary',
                'name_en' => 'Holiday Home Prep',
                'name_ru' => 'Товары для праздника',
                'image' => 'category/category-seeder/products_holiday.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Kitaplar',
                'name_en' => 'Books',
                'name_ru' => 'Книги',
                'image' => 'category/category-seeder/books.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Suratlar',
                'name_en' => 'Paintings',
                'name_ru' => 'Картины',
                'image' => 'category/category-seeder/picture.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Haýwanat üpjünçiligi',
                'name_en' => 'Pet Supplies',
                'name_ru' => 'Зоотовары',
                'image' => 'category/category-seeder/pet_supplies.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Sowgat kartlary',
                'name_en' => 'Gift Cards',
                'name_ru' => 'Подарочные сертификаты',
                'image' => 'category/category-seeder/gift_card.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Öý hojalyk harytlary',
                'name_en' => 'Home Accessories',
                'name_ru' => 'Для дома',
                'image' => 'category/category-seeder/for_home.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Kanselýariýa',
                'name_en' => 'Stationery',
                'name_ru' => 'Канлярские товары',
                'image' => 'category/category-seeder/stationery.png',
                'category_id' => null,
            ],
            [
                'name_tm' => 'Beýlekiler',
                'name_en' => 'Other',
                'name_ru' => 'Другие',
                'image' => 'category/category-seeder/other_group.png',
                'category_id' => null,
            ],
        ];

        $subCategories = [
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],    
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],    
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],    
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],   
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],    
            [
                'name_tm' => $faker->words(1, true),
                'name_en' => $faker->words(1, true),
                'name_ru' => $faker->words(1, true),
                'image' => 'category/category-seeder/other_group.png',
            ],    
        ];

        // <-- begin:: Parent Category -->
        foreach ($parentCategories as $parentCategory) 
        {
            Category::create([
                'name_tm' => $parentCategory['name_tm'],
                'name_en' => $parentCategory['name_en'],
                'name_ru' => $parentCategory['name_ru'],
                'image' => $parentCategory['image'],
                'category_id' => $parentCategory['category_id'],
            ]); 
        }
        // <-- end:: Parent Category -->

        for($i=0; $i<3; $i++){
            // <-- begin:: Sub Category -->
            foreach ($subCategories as $subCategory) 
            {
                Category::create([
                    'name_tm' => $subCategory['name_tm'],
                    'name_en' => $subCategory['name_en'],
                    'name_ru' => $subCategory['name_ru'],
                    'image' => $subCategory['image'],
                    'category_id' => $faker->numberBetween(1, 43),
                ]); 
            }
            // <-- end:: Sub Category -->
        }
    }
}
