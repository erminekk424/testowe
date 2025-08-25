<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SearchRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:/^[a-zA-Z0-9\s]+$/',
                'not_regex:/^\s+$/',
                'not_regex:/\s{3,}/',
            ],
            'filter.game' => [
                'nullable',
                'string',
                'uuid',
                'exists:games,uuid',
            ],
            'filter.type' => [
                'nullable',
                'string',
                Rule::enum(ProductType::class)],

        ];
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('query', '');

        $query = preg_replace('/[^a-zA-Z0-9\s]/', '', $query);

        $query = preg_replace('/\s+/', ' ', trim($query));

        $this->merge(['query' => $query]);
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        RateLimiter::hit($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        // 2 zapytania na sekundę
        $secondKey = $this->throttleKey().':second';
        if (RateLimiter::tooManyAttempts($secondKey, 2)) {
            $this->throwRateLimitException($secondKey, 'na sekundę');
        }

        // 30 zapytań na minutę (bardzo restrykcyjne)
        $minuteKey = $this->throttleKey().':minute';
        if (RateLimiter::tooManyAttempts($minuteKey, 30)) {
            $this->throwRateLimitException($minuteKey, 'na minutę');
        }

        //        // 200 zapytań na godzinę (długoterminowa ochrona)
        //        $hourKey = $this->throttleKey() . ':hour';
        //        if (RateLimiter::tooManyAttempts($hourKey, 200)) {
        //            $this->throwRateLimitException($hourKey, 'na godzinę');
        //        }

        // Ustaw liczniki z odpowiednimi czasami wygaśnięcia
        RateLimiter::hit($secondKey, 1);    // 1 sekunda
        RateLimiter::hit($minuteKey, 60);   // 1 minuta
        //        RateLimiter::hit($hourKey, 3600);   // 1 godzina
    }

    private function throwRateLimitException(string $key, string $period): void
    {
        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'query' => "Zbyt wiele zapytań wyszukiwania {$period}. Spróbuj ponownie za {$seconds} sekund.",
        ]);
    }

    public function throttleKey(): string
    {
        return 'search:'.$this->getClientIp();
    }

    public function getCleanQuery(): string
    {
        return $this->validated()['query'];
    }
}
