<?php

namespace App\Http\Requests;

use App\Rules\ArtistUsername;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Reserved system usernames kept here until FIX-API-08 centralizes them.
     *
     * @var list<string>
     */
    public const RESERVED_WORDS = [
        'admin',
        'api',
        'app',
        'assinart',
        'auth',
        'cadastro',
        'confirmacao-email',
        'contato',
        'dashboard',
        'editor',
        'email',
        'entrar',
        'esqueci-senha',
        'home',
        'login',
        'logout',
        'null',
        'perfil',
        'playground',
        'privacidade',
        'r',
        'register',
        'root',
        'sac',
        'settings',
        'signin',
        'signup',
        'status',
        'termos',
        'undefined',
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Suporta tanto agree_terms (snake_case) quanto agreeTerms (camelCase),
     * além de sanitizar o @ inicial e espaços do username.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('agreeTerms') && !$this->has('agree_terms')) {
            $this->merge([
                'agree_terms' => $this->input('agreeTerms'),
            ]);
        }

        if ($this->has('username')) {
            $this->merge([
                'username' => ArtistUsername::normalize($this->input('username')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:2',
                'max:30',
                new ArtistUsername(),
                Rule::unique('artists', 'username'),
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:artists,email'],
            'password' => ['required', 'string', 'min:6'],
            'agree_terms' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(ArtistUsername::messages(), [
            'name.required' => 'O nome completo é obrigatório.',
            'name.max' => 'O nome não pode exceder 100 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O e-mail não pode exceder 255 caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter pelo menos 6 caracteres.',
            'agree_terms.required' => 'Você precisa concordar com os termos de uso.',
            'agree_terms.accepted' => 'Você precisa concordar com os termos de uso.',
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
