<?php
/**
 * *************************
 * Created by PhpStorm.
 * User: Mayur Tagadiya
 * Date: 8/8/2015
 * Time: 11:20 AM
 ***************************
 * Modified: Brijesh Khatri
 * StartDate: 12Oct2015
 * *************************
 */
namespace App\Traits;


/**
 * Class ModelEventLogger
 * @package App\DB\Traits
 *
 *  Automatically Log Add, Update, Delete events of Model.
 */
trait FilterDataBeforeSave
{
    /**
     * Automatically boot with Model, and register Events handler.
     */
    protected static function bootFilterDataBeforeSave()
    {
        //pre("Tsete");
    }

   
} 