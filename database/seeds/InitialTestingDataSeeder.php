<?php

use Illuminate\Database\Seeder;
use App\Api\Models\CmsRole;
use App\Api\Models\User;
use App\Api\Models\Language;
use App\Api\Constants\RoleConstants;

class InitialTestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->generateUserAndRoles();

        $this->generateLanguage();
    }

    /**
     * Generate User and Roles
     */
    private function generateUserAndRoles()
    {
        CmsRole::truncate();

        //Developer roles
        /** @var CmsRole $developer_role */
        $developer_role = CmsRole::create([
            "name" => RoleConstants::DEVELOPER,
            "is_developer" => true,
            "allow_structure" => true,
            "allow_content" => true,
            "allow_user" => true,
            "updated_by" => "SYSTEM"
        ]);

        //Administrator roles
        /** @var CmsRole $admin_role */
        $admin_role = CmsRole::create([
            "name" => RoleConstants::ADMINISTRATOR,
            "is_developer" => false,
            "allow_structure" => true,
            "allow_content" => true,
            "allow_user" => true,
            "updated_by" => "SYSTEM"
        ]);

        //Editorial roles
        CmsRole::create([
            "name" => RoleConstants::EDITORIAL,
            "is_developer" => false,
            "allow_structure" => false,
            "allow_content" => true,
            "allow_user" => false,
            "updated_by" => "SYSTEM"
        ]);

        User::truncate();

        User::create([
            "name" => "QUO Developer",
            "email" => "developers@cms.com",
            "password" => 'developers',
            'role_id' => $developer_role->id
        ]);

        User::create([
            "name" => "Administrator",
            "email" => "admin@cms.com",
            "password" => 'admin',
            'role_id' => $admin_role->id
        ]);
    }

    /**
     * Generate Language
     */
    private function generateLanguage()
    {
        Language::firstOrCreate([
            'code' => config('cms.' . get_cms_application() . '.main_language.code'),
            'name' => config('cms.' . get_cms_application() . '.main_language.name'),
            'locale' => config('cms.' . get_cms_application() . '.main_language.locale'),
            'hreflang' => config('cms.' . get_cms_application() . '.main_language.hreflang')
        ]);
    }
}
