<?php

namespace App\Models;

use App\Mail\NewAccountMail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, HasUuids;

    //
    protected $guarded = [];

    /**
     * The auto-incrementing id stays the primary key; only `guid` gets a UUID.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    public static function findByEmailAndPhoneNumber($email, $phone_number): ?self
    {
        //        dd($email, $phone_number);
        return (new self)->where('email', $email)->where('phone_number', $phone_number)->first();

    }

    public static function createNewCustomer($data): self|array
    {
        /** @var self $customer */
        $customer = self::find($data['id'] ?? 0);
        // if no customer is found, we check on the e-mail and phone number; if they match we use that customer to
        // prevent database polution.
        if (! $customer) {
            $customer = self::findByEmailAndPhoneNumber($data['email'], $data['phone_number']);
        }

        if ($data['create_account'] && empty($customer?->user_id)) {
            if ($user = User::where('email', $data['email'])->first()) {
                $data['user_id'] = $user->id;
            } else {
                // Het wachtwoord is willekeurig; de klant stelt zelf een wachtwoord in via de link in de welkomstmail.
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt(Str::random(21)),
                ]);
                $data['user_id'] = $user->id;

                Mail::to($user->email)->send(new NewAccountMail($user, Password::createToken($user)));
            }
        }
        unset($data['create_account']);
        if (! $customer) {
            unset($data['id']);
            $result = self::create($data);
        } else {
            $data['id'] = $customer->id;
            $customer->update($data);
            $result = self::find($data['id']);
        }

        return $result;
    }
}
