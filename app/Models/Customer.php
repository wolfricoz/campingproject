<?php

namespace App\Models;

use App\Mail\NewAccountMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    /**
     * The screens show the phone number in the notation people recognise, while
     * the database keeps it in one machine notation.
     *
     * @var list<string>
     */
    protected $appends = ['phone_number_formatted'];

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['guid'];
    }

    public function arrangements(): HasMany
    {
        return $this->hasMany(Arrangement::class);
    }

    /**
     * Keeps every phone number in one notation, no matter how it was typed in.
     *
     * The desk enters `06-24815903`, the booking screen `06 12345678` and the
     * seeder `0032-478112094`; without this they are three different values and
     * a customer can never be looked up again.
     */
    public static function normalisePhoneNumber(?string $phoneNumber): ?string
    {
        if ($phoneNumber === null) {
            return null;
        }

        $withoutPlus = preg_replace('/^\+/', '00', trim($phoneNumber)) ?? '';

        return preg_replace('/\D/', '', $withoutPlus) ?? '';
    }

    /**
     * Normalises the phone number on the way into the database, so the stored
     * notation can never depend on which screen it came from.
     *
     * @return Attribute<?string, ?string>
     */
    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => self::normalisePhoneNumber($value),
        );
    }

    /**
     * The phone number as the desk reads it out loud.
     *
     * Only the two notations we can be certain about are dressed up: a Dutch
     * mobile number and an international number. Anything else is shown the way
     * it is stored, because guessing at an area code makes it harder to read
     * instead of easier.
     *
     * @return Attribute<string, never>
     */
    protected function phoneNumberFormatted(): Attribute
    {
        return Attribute::get(function (): string {
            $digits = (string) $this->phone_number;

            if (str_starts_with($digits, '00')) {
                return '+'.substr($digits, 2);
            }

            if (strlen($digits) === 10 && str_starts_with($digits, '06')) {
                return '06-'.substr($digits, 2);
            }

            return $digits;
        });
    }

    public static function findByEmailAndPhoneNumber(string $email, ?string $phone_number): ?self
    {
        return (new self)
            ->where('email', $email)
            ->where('phone_number', self::normalisePhoneNumber($phone_number))
            ->first();
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
            $customer = self::findByEmailAndPhoneNumber($data['email'], $data['phone_number'] ?? null);
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

    public function anonymize(): void
    {
        if ($this->user_id) {
            // since the user hold an email; it has to be deleted.
            User::find($this->user_id)?->delete();
        }

        $this->update(
            [
                'name' => 'Klant Geanonimiseerd',
                'email' => $this->guid.'@syntec-camping.nl',
                'phone_number' => '0612345678',
                'street_name' => '**',
                'street_number' => '**',
                'city' => '**',
                'postal_code' => '**',
                'country' => '**',
                'user_id' => null, // we unlink the customer from the user.

            ],
        );
    }
}
