<?php

use Illuminate\Database\Seeder;
use App\Helpers\Cfg;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Cfg::set('AcceptedCardTypeList', 'Visa,MasterCard,Discover,American Express,JCB,Diners Club');
        Cfg::set('ClientsProfileOptionalFields', '');
        Cfg::set('ClientsProfileUneditableFields', ''); 
        Cfg::set('WhitelistedIPs', '');
        Cfg::set('MailEncoding', '');
        Cfg::set('AllowAutoAuth', '');
    }
}
