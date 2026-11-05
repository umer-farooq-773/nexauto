<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentEmail = auth()->user()->email;

        return [
            'f_name' => ['required', 'string', 'max:100'],
            'l_name' => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'max:200', function ($attribute, $value, $fail) use ($currentEmail) {
                if ($value !== $currentEmail) {
                    $fail('You cannot change your email. Please contact the admin!');
                }
            }],
            'file' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }

    /**
     * Custom human-friendly error messages.
     */
    public function messages(): array
    {
        return [
            'file.image'    => 'The profile picture must be a valid image.',
            'file.mimes'    => 'Only JPG, JPEG, and PNG images are allowed.',
            'file.max'      => 'The profile picture must not be larger than 2 MB (2048 KB). Please choose a smaller image.',
        ];
    }

    /**
     * Detect when PHP silently drops the upload because it exceeded
     * php.ini upload_max_filesize / post_max_size before Laravel
     * can even read it. In that case $_FILES is empty but the
     * Content-Length header is larger than post_max_size.
     */
    protected function prepareForValidation(): void
    {
        $serverMaxBytes = $this->getPhpMaxPostBytes();

        if (
            $serverMaxBytes > 0
            && (int) $this->server('CONTENT_LENGTH', 0) > $serverMaxBytes
            && empty($_FILES)
        ) {
            // Inject a fake file error so the validator can report it properly
            $maxKb = (int) ($serverMaxBytes / 1024);
            $this->merge(['_php_upload_dropped' => true]);
        }
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $serverMaxBytes = $this->getPhpMaxPostBytes();

        if (
            $serverMaxBytes > 0
            && (int) $this->server('CONTENT_LENGTH', 0) > $serverMaxBytes
            && empty($_FILES)
        ) {
            $maxMb = round($serverMaxBytes / 1024 / 1024, 1);
            $validator->after(function ($v) use ($maxMb) {
                $v->errors()->add(
                    'file',
                    "The uploaded file exceeds the server's maximum allowed size of {$maxMb} MB. Please choose a smaller image."
                );
            });
        }
    }

    /** Convert php.ini shorthand (2M, 8M …) to bytes. */
    private function getPhpMaxPostBytes(): int
    {
        $val = trim(ini_get('post_max_size'));
        if ($val === '' || $val === '0') return 0;
        $last = strtolower($val[strlen($val) - 1]);
        $num  = (int) $val;
        return match ($last) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }
}
