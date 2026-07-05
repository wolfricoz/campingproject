<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    //
    protected $guarded = [];

    public static function findByEmailAndPhoneNumber($email, $phone_number){
//        dd($email, $phone_number);
        return (new self())->where('email', $email)->where('phone_number', $phone_number)->first();

    }
}
