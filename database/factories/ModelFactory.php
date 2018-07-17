<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Api\Models\User::class, function (Faker\Generator $faker) {
    static $password, $is_active;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'role_id' => function () {
            if (App\Api\Models\CmsRole::all()->count() > 1) {
                return App\Api\Models\CmsRole::all()->random()->id;
            } else {
                return factory(App\Api\Models\CmsRole::class)->create()->id;
            }
        },
        'is_active' => $is_active ?: $is_active = true
    ];
});

$factory->define(App\Api\Models\Site::class, function (Faker\Generator $faker) {
    return [
        'domain_name' => $faker->unique()->domainName,
        'description' => $faker->realText(),
        'is_active' => $faker->boolean()
    ];
});

$factory->define(App\Api\Models\CmsRole::class, function (Faker\Generator $faker) {
    static $updated_by;

    return [
        'is_developer' => $faker->boolean(),
        'allow_structure' => $faker->boolean(),
        'allow_content' => $faker->boolean(),
        'allow_user' => $faker->boolean(),
        'updated_by' => $updated_by ?: $updated_by = \App\Api\Constants\LogConstants::SYSTEM
    ];
});

$factory->define(App\Api\Models\RedirectUrl::class, function (Faker\Generator $faker) {
    static $status_code;

    return [
        'status_code' => $status_code ?: $status_code = \Symfony\Component\HttpFoundation\Response::HTTP_FOUND,
        'source_url' => $faker->slug(),
        'destination_url' => $faker->url,
        'is_active' => $faker->boolean(),
        'site_id' => function () {
            return factory(App\Api\Models\Site::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\Component::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText()
    ];
});

$factory->define(App\Api\Models\ComponentOption::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'is_required' => $faker->boolean(),
        'is_active' => $faker->boolean(),
        'component_id' => function () {
            return factory(App\Api\Models\Component::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\Template::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'site_id' => function () {
            return factory(App\Api\Models\Site::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\TemplateItem::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'template_id' => function () {
            return factory(App\Api\Models\Template::class)->create()->id;
        },
        'component_id' => null
//        'component_id' => function () {
//            return factory(App\Api\Models\Component::class)->create()->id;
//        }
//        'display_order' => $faker->randomNumber(1, true)
    ];
});

$factory->define(App\Api\Models\TemplateItemOption::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'is_required' => $faker->boolean(),
        'is_active' => $faker->boolean(),
        'template_item_id' => function () {
            return factory(App\Api\Models\TemplateItem::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\Page::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'friendly_url' => $faker->unique()->slug(),
        'is_active' => $faker->boolean(),
        'template_id' => function () {
            return factory(App\Api\Models\Template::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\PageItem::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'page_id' => function () {
            return factory(App\Api\Models\Page::class)->create()->id;
        },
        'component_id' => null,
        'global_item_id' => null
//        'component_id' => function () {
//            return factory(App\Api\Models\Component::class)->create()->id;
//        }
//        'display_order' => $faker->randomNumber(1, true)
    ];
});

$factory->define(App\Api\Models\PageItemOption::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'page_item_id' => function () {
            return factory(App\Api\Models\PageItem::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\GlobalItem::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'is_active' => $faker->boolean(),
        'site_id' => function () {
            return factory(App\Api\Models\Site::class)->create()->id;
        },
        'component_id' => null
//        'component_id' => function () {
//            return factory(App\Api\Models\Component::class)->create()->id;
//        }
    ];
});

$factory->define(App\Api\Models\GlobalItemOption::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(10, true),
        'variable_name' => $faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}'),
        'description' => $faker->realText(),
        'is_required' => $faker->boolean(),
        'is_active' => $faker->boolean(),
        'global_item_id' => function () {
            return factory(App\Api\Models\GlobalItem::class)->create()->id;
        }
    ];
});

$factory->define(App\Api\Models\Language::class, function (Faker\Generator $faker) {
    return [
        'code' => $faker->unique()->word,
        'name' => $faker->unique()->word,
        'hreflang' => $faker->unique()->word,
        'locale' => $faker->unique()->word,
        'is_active' => $faker->boolean()
    ];
});

$factory->define(App\Api\Models\CategoryName::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->word
    ];
});