<?php

return [
    'home' => [
        'title' => [
            'uk' => 'SimpleShop - Інтернет-магазин якісних товарів з доставкою по Україні',
            'en' => 'SimpleShop - Online Store for Quality Products with Ukraine Delivery',
        ],
        'description' => [
            'uk' => 'Великий вибір якісних товарів за найкращими цінами. Швидка доставка по всій Україні. Гарантія якості та професійна підтримка клієнтів.',
            'en' => 'Wide selection of quality products at the best prices. Fast delivery throughout Ukraine. Quality guarantee and professional customer support.',
        ],
        'keywords' => [
            'uk' => ['інтернет магазин', 'товари', 'доставка', 'україна', 'якість', 'ціна', 'швидка доставка', 'онлайн покупки'],
            'en' => ['online store', 'products', 'delivery', 'ukraine', 'quality', 'price', 'fast delivery', 'online shopping'],
        ],
        'structured_data' => [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'SimpleShop'),
            'url' => config('app.url'),
            'description' => 'Інтернет-магазин якісних товарів з доставкою по Україні',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'UA',
                'addressLocality' => 'Київ',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+380123456789',
                'contactType' => 'customer service',
                'availableLanguage' => ['Ukrainian', 'English'],
            ],
        ],
    ],

    'category' => [
        'title_template' => [
            'uk' => 'Категорія %s - Великий вибір товарів | %s',
            'en' => '%s Category - Wide Product Selection | %s',
        ],
        'description_template' => [
            'uk' => 'Великий вибір товарів у категорії %s за найкращими цінами. Швидка доставка по Україні. Гарантія якості від %s.',
            'en' => 'Wide selection of products in %s category at the best prices. Fast delivery across Ukraine. Quality guarantee from %s.',
        ],
        'keywords_template' => [
            'uk' => ['%s', 'товари %s', 'купити %s', 'ціна %s', 'доставка', 'україна'],
            'en' => ['%s', '%s products', 'buy %s', '%s price', 'delivery', 'ukraine'],
        ],
        'structured_data_template' => [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => '%s',
            'description' => '%s',
            'url' => '%s',
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => '%d',
            ],
        ],
    ],

    'product' => [
        'title_template' => [
            'uk' => 'Купити %s за %s грн - %s | %s',
            'en' => 'Buy %s for %s UAH - %s | %s',
        ],
        'description_template' => [
            'uk' => 'Купити %s за найкращою ціною %s грн. %s. Швидка доставка по Україні. Гарантія якості.',
            'en' => 'Buy %s at the best price %s UAH. %s. Fast delivery across Ukraine. Quality guarantee.',
        ],
        'keywords_template' => [
            'uk' => ['%s', 'купити %s', 'ціна %s', '%s грн', 'доставка', 'гарантія'],
            'en' => ['%s', 'buy %s', '%s price', '%s uah', 'delivery', 'guarantee'],
        ],
        'structured_data_template' => [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => '%s',
            'description' => '%s',
            'image' => '%s',
            'brand' => [
                '@type' => 'Brand',
                'name' => '%s',
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => '%s',
                'priceCurrency' => 'UAH',
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => '%s',
                ],
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '%s',
                'reviewCount' => '%s',
            ],
        ],
    ],

    'search' => [
        'title' => [
            'uk' => 'Пошук "%s" - Результати пошуку | %s',
            'en' => 'Search "%s" - Search Results | %s',
        ],
        'description' => [
            'uk' => 'Результати пошуку за запитом "%s". Знайдено %d товарів. Швидка доставка по Україні.',
            'en' => 'Search results for "%s". Found %d products. Fast delivery across Ukraine.',
        ],
        'keywords' => [
            'uk' => ['пошук', '%s', 'товари', 'інтернет магазин', 'доставка'],
            'en' => ['search', '%s', 'products', 'online store', 'delivery'],
        ],
    ],

    'cart' => [
        'title' => [
            'uk' => 'Кошик покупок - %d товарів | %s',
            'en' => 'Shopping Cart - %d items | %s',
        ],
        'description' => [
            'uk' => 'Ваш кошик містить %d товарів на суму %s грн. Оформіть замовлення для швидкої доставки.',
            'en' => 'Your cart contains %d items worth %s UAH. Place your order for fast delivery.',
        ],
        'robots' => 'noindex,follow',
    ],

    'checkout' => [
        'title' => [
            'uk' => 'Оформлення замовлення - %d товарів | %s',
            'en' => 'Checkout - %d items | %s',
        ],
        'description' => [
            'uk' => 'Оформіть замовлення на %d товарів. Швидка доставка по Україні. Безпечна оплата.',
            'en' => 'Complete your order for %d items. Fast delivery across Ukraine. Secure payment.',
        ],
        'robots' => 'noindex,follow',
    ],

    'user' => [
        'profile' => [
            'title' => [
                'uk' => 'Особистий кабінет - Профіль | %s',
                'en' => 'User Profile - Account | %s',
            ],
            'description' => [
                'uk' => 'Управління особистим кабінетом, перегляд замовлень та налаштування профілю.',
                'en' => 'Manage your account, view orders and configure profile settings.',
            ],
            'robots' => 'noindex,follow',
        ],
        'orders' => [
            'title' => [
                'uk' => 'Мої замовлення - %d замовлень | %s',
                'en' => 'My Orders - %d orders | %s',
            ],
            'description' => [
                'uk' => 'Історія ваших замовлень та їх статус. Відстеження доставки та повторні замовлення.',
                'en' => 'Your order history and status. Delivery tracking and reorders.',
            ],
            'robots' => 'noindex,follow',
        ],
    ],

    'auth' => [
        'login' => [
            'title' => [
                'uk' => 'Вхід до особистого кабінету | %s',
                'en' => 'Login to Account | %s',
            ],
            'description' => [
                'uk' => 'Увійдіть до особистого кабінету для управління замовленнями та налаштуваннями.',
                'en' => 'Login to your account to manage orders and settings.',
            ],
            'robots' => 'noindex,follow',
        ],
        'register' => [
            'title' => [
                'uk' => 'Реєстрація нового акаунту | %s',
                'en' => 'Create New Account | %s',
            ],
            'description' => [
                'uk' => 'Створіть акаунт для зручних покупок, відстеження замовлень та персональних знижок.',
                'en' => 'Create account for convenient shopping, order tracking and personal discounts.',
            ],
            'robots' => 'noindex,follow',
        ],
    ],

    'error_pages' => [
        '404' => [
            'title' => [
                'uk' => 'Сторінка не знайдена (404) | %s',
                'en' => 'Page Not Found (404) | %s',
            ],
            'description' => [
                'uk' => 'Запитувана сторінка не знайдена. Поверніться на головну або скористайтеся пошуком.',
                'en' => 'The requested page was not found. Return to homepage or use search.',
            ],
            'robots' => 'noindex,follow',
        ],
        '500' => [
            'title' => [
                'uk' => 'Помилка сервера (500) | %s',
                'en' => 'Server Error (500) | %s',
            ],
            'description' => [
                'uk' => 'Виникла тимчасова помилка сервера. Спробуйте пізніше або зверніться до підтримки.',
                'en' => 'A temporary server error occurred. Please try again later or contact support.',
            ],
            'robots' => 'noindex,follow',
        ],
    ],

    'admin' => [
        'robots' => 'noindex,nofollow',
        'exclude_from_sitemap' => true,
    ],

    'faq_schema' => [
        'enabled' => true,
        'auto_generate' => true,
        'max_questions' => 10,
        'templates' => [
            'category' => [
                'uk' => [
                    'questions' => [
                        'Які товари представлені в категорії %s?',
                        'Чи є доставка товарів категорії %s?',
                        'Які гарантії на товари %s?',
                        'Як замовити товари з категорії %s?',
                        'Чи можна повернути товар з категорії %s?',
                    ],
                    'answers' => [
                        'У категорії %s представлено %d якісних товарів різних брендів за доступними цінами.',
                        'Так, ми здійснюємо доставку всіх товарів категорії %s по всій Україні протягом 1-3 робочих днів.',
                        'На всі товари категорії %s діє офіційна гарантія виробника від 6 місяців до 2 років.',
                        'Для замовлення товарів з категорії %s додайте їх у кошик та оформіть замовлення онлайн.',
                        'Так, товари категорії %s можна повернути протягом 14 днів згідно з законом України.',
                    ],
                ],
                'en' => [
                    'questions' => [
                        'What products are available in %s category?',
                        'Is delivery available for %s category products?',
                        'What warranties are provided for %s products?',
                        'How to order products from %s category?',
                        'Can I return products from %s category?',
                    ],
                    'answers' => [
                        'The %s category features %d quality products from various brands at affordable prices.',
                        'Yes, we deliver all %s category products throughout Ukraine within 1-3 business days.',
                        'All %s category products come with official manufacturer warranty from 6 months to 2 years.',
                        'To order products from %s category, add them to cart and complete online checkout.',
                        'Yes, %s category products can be returned within 14 days according to Ukrainian law.',
                    ],
                ],
            ],
            'product' => [
                'uk' => [
                    'questions' => [
                        'Які характеристики товару %s?',
                        'Чи є %s в наявності?',
                        'Скільки коштує доставка %s?',
                        'Яка гарантія на %s?',
                        'Як оплатити %s?',
                        'Чи можна повернути %s?',
                    ],
                    'answers' => [
                        'Детальні характеристики %s наведені в описі товару. За додатковими питаннями звертайтеся до консультантів.',
                        'Так, %s є в наявності. Актуальну кількість можна уточнити у консультантів.',
                        'Доставка %s коштує від 50 грн по Україні, безкоштовна доставка при замовленні від 1000 грн.',
                        'На %s діє офіційна гарантія виробника. Термін гарантії вказано в описі товару.',
                        'Оплата %s можлива готівкою при отриманні, картою онлайн або банківським переказом.',
                        'Так, %s можна повернути протягом 14 днів у випадку невідповідності або браку.',
                    ],
                ],
                'en' => [
                    'questions' => [
                        'What are the specifications of %s?',
                        'Is %s in stock?',
                        'How much does %s delivery cost?',
                        'What warranty does %s have?',
                        'How to pay for %s?',
                        'Can I return %s?',
                    ],
                    'answers' => [
                        'Detailed specifications of %s are provided in the product description. Contact consultants for additional questions.',
                        'Yes, %s is in stock. Contact consultants for current quantity availability.',
                        '%s delivery costs from 50 UAH across Ukraine, free delivery for orders over 1000 UAH.',
                        '%s comes with official manufacturer warranty. Warranty period is specified in product description.',
                        'Payment for %s is available by cash on delivery, online card payment or bank transfer.',
                        'Yes, %s can be returned within 14 days in case of non-compliance or defects.',
                    ],
                ],
            ],
        ],
    ],

    'static_pages' => [
        'about' => [
            'title' => [
                'uk' => 'Про нас - Історія та місія SimpleShop | %s',
                'en' => 'About Us - SimpleShop History and Mission | %s',
            ],
            'description' => [
                'uk' => 'Дізнайтеся більше про SimpleShop: нашу історію, місію та підхід до якості товарів і сервісу.',
                'en' => 'Learn more about SimpleShop: our history, mission and approach to product quality and service.',
            ],
            'keywords' => [
                'uk' => ['про нас', 'simpleshop', 'історія', 'місія', 'якість', 'сервіс'],
                'en' => ['about us', 'simpleshop', 'history', 'mission', 'quality', 'service'],
            ],
        ],
        'contacts' => [
            'title' => [
                'uk' => 'Контакти - Зв\'яжіться з SimpleShop | %s',
                'en' => 'Contacts - Get in Touch with SimpleShop | %s',
            ],
            'description' => [
                'uk' => 'Контактна інформація SimpleShop: телефони, адреса, графік роботи та форма зворотного зв\'язку.',
                'en' => 'SimpleShop contact information: phones, address, working hours and feedback form.',
            ],
            'keywords' => [
                'uk' => ['контакти', 'телефон', 'адреса', 'підтримка', 'зворотний зв\'язок'],
                'en' => ['contacts', 'phone', 'address', 'support', 'feedback'],
            ],
        ],
        'delivery' => [
            'title' => [
                'uk' => 'Доставка та оплата - Умови та тарифи | %s',
                'en' => 'Delivery and Payment - Terms and Rates | %s',
            ],
            'description' => [
                'uk' => 'Детальна інформація про доставку товарів по Україні, способи оплати та умови повернення.',
                'en' => 'Detailed information about product delivery across Ukraine, payment methods and return conditions.',
            ],
            'keywords' => [
                'uk' => ['доставка', 'оплата', 'тарифи', 'умови', 'повернення', 'україна'],
                'en' => ['delivery', 'payment', 'rates', 'terms', 'returns', 'ukraine'],
            ],
        ],
        'privacy' => [
            'title' => [
                'uk' => 'Політика конфіденційності - Захист персональних даних | %s',
                'en' => 'Privacy Policy - Personal Data Protection | %s',
            ],
            'description' => [
                'uk' => 'Політика конфіденційності SimpleShop щодо обробки та захисту персональних даних клієнтів.',
                'en' => 'SimpleShop privacy policy regarding processing and protection of customer personal data.',
            ],
            'keywords' => [
                'uk' => ['політика конфіденційності', 'персональні дані', 'захист', 'приватність'],
                'en' => ['privacy policy', 'personal data', 'protection', 'privacy'],
            ],
            'robots' => 'index,follow',
        ],
        'terms' => [
            'title' => [
                'uk' => 'Умови використання - Правила та положення | %s',
                'en' => 'Terms of Use - Rules and Regulations | %s',
            ],
            'description' => [
                'uk' => 'Умови використання сайту SimpleShop, правила покупок та відповідальність сторін.',
                'en' => 'SimpleShop website terms of use, shopping rules and party responsibilities.',
            ],
            'keywords' => [
                'uk' => ['умови використання', 'правила', 'положення', 'відповідальність'],
                'en' => ['terms of use', 'rules', 'regulations', 'responsibility'],
            ],
            'robots' => 'index,follow',
        ],
    ],

    'blog' => [
        'index' => [
            'title' => [
                'uk' => 'Блог - Корисні статті та новини | %s',
                'en' => 'Blog - Useful Articles and News | %s',
            ],
            'description' => [
                'uk' => 'Корисні статті про товари, поради з вибору та новини від SimpleShop.',
                'en' => 'Useful articles about products, selection tips and news from SimpleShop.',
            ],
            'keywords' => [
                'uk' => ['блог', 'статті', 'новини', 'поради', 'товари'],
                'en' => ['blog', 'articles', 'news', 'tips', 'products'],
            ],
        ],
        'post' => [
            'title_template' => [
                'uk' => '%s - Корисні поради | %s',
                'en' => '%s - Useful Tips | %s',
            ],
            'description_template' => [
                'uk' => '%s. Корисна інформація та поради від експертів SimpleShop.',
                'en' => '%s. Useful information and tips from SimpleShop experts.',
            ],
            'keywords_template' => [
                'uk' => ['%s', 'поради', 'інформація', 'блог', 'експерти'],
                'en' => ['%s', 'tips', 'information', 'blog', 'experts'],
            ],
        ],
    ],

    'defaults' => [
        'sitemap' => [
            'priority' => 0.5,
            'changefreq' => 'monthly',
            'include' => true,
        ],
        'robots' => 'index,follow',
        'language' => 'uk',
        'charset' => 'UTF-8',
    ],

    'regional' => [
        'ukraine' => [
            'currency' => 'UAH',
            'currency_symbol' => 'грн',
            'phone_format' => '+380XXXXXXXXX',
            'address_format' => 'city, region, Ukraine',
            'business_hours' => 'Пн-Пт: 9:00-18:00, Сб: 10:00-16:00',
        ],
    ],

    'social_media' => [
        'facebook' => 'https://facebook.com/simpleshop.ua',
        'instagram' => 'https://instagram.com/simpleshop.ua',
        'telegram' => 'https://t.me/simpleshop_ua',
        'viber' => 'viber://chat?number=%2B380123456789',
    ],

    'breadcrumbs' => [
        'enabled' => true,
        'home_title' => [
            'uk' => 'Головна',
            'en' => 'Home',
        ],
        'separator' => ' / ',
        'structured_data' => true,
    ],
];
