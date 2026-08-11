<?php

namespace App\Models;

use App\Mail\NewAccountMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    public static function findByEmailAndPhoneNumber(string $email, ?string $phone_number): ?self
    {
        return (new self)->where('email', $email)->where('phone_number', $phone_number)->first();
    }

    /**
     * Updates the matching customer, or creates a new one.
     *
     * When no customer is found on id we fall back to the e-mail and phone number
     * combination, so the same person does not end up in the database twice.
     *
     * @param  array<string, mixed>  $data
     */
    public static function createNewCustomer(array $data): self
    {
        $customer = self::find($data['id'] ?? 0);

        if (! $customer) {
            $customer = self::findByEmailAndPhoneNumber($data['email'], $data['phone_number']);
        }

        if ($data['create_account'] && ! $customer?->user_id) {
            if ($user = User::where('email', $data['email'])->first()) {
                $data['user_id'] = $user->id;
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt(Str::random(21)),
                ]);
                $user->assignRole('customer');
                $data['user_id'] = $user->id;

                Mail::to($user->email)->send(new NewAccountMail($user, Password::createToken($user)));
                event(new Registered($user));
            }
        }
        unset($data['create_account']);
        if (! $customer) {
            unset($data['id']);

            return self::create($data);
        }

        $data['id'] = $customer->id;
        $customer->update($data);

        return $customer->refresh();
    }
}
