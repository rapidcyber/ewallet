<?php

namespace App\Traits;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumber;

trait WithValidPhoneNumber
{
    /**
     * Validated if a international phonenumber provided is valid.
     * 
     * @param mixed $phonenumber - should include the phone code (+##)
     * @return bool
     */
    public function is_valid_phonenumber(string $phonenumber): bool
    {
        if (!str_starts_with($phonenumber, '+')) {
            $phonenumber = '+' . $phonenumber;
        }
        $util = PhoneNumberUtil::getInstance();
        try {
            $parsed = $util->parse($phonenumber);
            $isValid = $util->isValidNumber($parsed);

            return $isValid;
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Return phone information based on the national number and iso combination.
     * Returns false if phone number and iso combination is not valid.
     * 
     * @param string $phone
     * @param string|null $isoCode
     * @return \libphonenumber\PhoneNumber|false
     */
    public function phonenumber_info(string $phone, string|null $isoCode = null): PhoneNumber|false
    {
        $util = PhoneNumberUtil::getInstance();
        try {
            $parsed = $util->parse($phone, $isoCode);
            $is_valid = $util->isValidNumber($parsed);

            if ($is_valid == false) {
                return false;
            }

            return $parsed;
        } catch (NumberParseException $e) {
            return false;
        }
    }

    public function format_phone_number_for_saving($contact_number, $phone_iso)
    {
        $validated_phone_number = $this->phonenumber_info($contact_number, $phone_iso);

        if (! $validated_phone_number) {
            return false;
        }

        return $validated_phone_number->getCountryCode() . $validated_phone_number->getNationalNumber();
    }

    public function format_phone_number($contact_number, $phone_iso)
    {
        $validated_phone_number = $this->phonenumber_info($contact_number, $phone_iso);

        if (! $validated_phone_number) {
            return $contact_number;
        }

        return '(+' . $validated_phone_number->getCountryCode() . ') ' . $validated_phone_number->getNationalNumber();
    }

    public function format_phone_number_for_display($contact_number, $phone_iso)
    {
        $validated_phone_number = $this->phonenumber_info($contact_number, $phone_iso);

        if (! $validated_phone_number) {
            return '+' . $contact_number;
        }

        return '+' . $validated_phone_number->getCountryCode() . $validated_phone_number->getNationalNumber();
    }
}
