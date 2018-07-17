<?php

return [
    "application_name" => env('CMS_APPLICATION_NAME', 'cms'),
    "domain" => ($domain = env("CMS_API_DOMAIN", "http://cms-api.dev")),
    "api" => [
        "domain" => "$domain/api/helpers/",
        "cache_ttl" => env("CMS_API_CACHE_TTL", 60),
        'form_token' => env("CMS_FORM_TOKEN", "YofihgsHXIObx9Nvz0smAA1plgYcozN1mMUwqjOe"),
        "models" => [
            "site" => ($site = "site"),
            "redirectUrl" => ($redirectUrl = "redirectUrl"),
            "page" => ($page = "page"),
            "globalItem" => ($globalItem = "globalItems"),
            "siteTranslation" => ($siteTranslation = "translation"),
        ],
        "urls" => [
            "root_site" => ($rootSite = "$site/:domain_name"),
            "helpers" => [
                "get_site" => "$rootSite",
                "get_site_map_data" => "$rootSite/sitemap",
                "get_site_map_xml" => "$rootSite/:language_code/sitemap.xml",
                "post_get_redirect_url" => "$rootSite/$redirectUrl",
                "get_site_translations" => "$rootSite/$siteTranslation" . "s/:language_code",
                "get_item_site_translations" => "$rootSite/$siteTranslation/:item_option_id/:language_code",
                "post_get_page" => "$rootSite/$page",
                "post_get_parent_pages_data" => "$rootSite/$page/parents",
                "post_get_child_pages_data" => "$rootSite/$page/children",
                "post_get_pages_search_by_categories" => "$rootSite/$page/search-by-categories",
                "post_get_pages_search_by_template" => "$rootSite/$page/search-by-template",
                "get_global_items" => "$rootSite/$globalItem/:language_code",
                "get_form_property_data" => "$rootSite/form/:variable_name/:language_code",
                "post_form_property_data" => "$rootSite/form/:variable_name/:language_code",
            ]
        ]
    ],

    "uploads_path" => ($uploadsPath = 'uploads'),
    "crops_path" => ($cropsPath = '_crops'),
    "auto_image_optimizer_enabled" => true,

    "friendly_url_prefix" => '',

    "homepage_path" => 'homepage',

    "mockup" => [
        "mode"                  => env("CMS_FRONTEND_MODE", false),
        "views_path"            => "mockup",
        "public_asset_path"     => "mockup",

        // Required**
        "template_name"         => env("APP_NAME", "watwutaram")
    ],

    "landing" => [
        "mode"                  => env("CMS_LANDING_PAGE_MODE", false),
        "landing_folder"        => env("CMS_LANDING_PAGE_FOLDER", "landing"),
        "landing_page"          => env("CMS_LANDING_PAGE_FILENAME", "index.html")
    ],

    //GEOIP
    "geoip_url" => env("GEOIP_URL", "http://api.quo-staging.com/"),
    "geoip_app_id" => env("GEOIP_APP_ID", "DbmnpP2QqGd2T4YH"),
    "geoip_app_token" => env("GEOIP_APP_TOKEN", "QUOAPI-2804c8cd-f371-4c04-baf3-c42ccaee2f30"),

    "default_currency" => "USD",
    "default_iso_code" => "US",
    "default_language_code" => "EN",
    "default_language_name" => "English"
];
