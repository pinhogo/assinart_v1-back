<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
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

        if ($this->has('username') && is_string($this->input('username'))) {
            $clean = strtolower(trim($this->input('username')));
            $clean = ltrim($clean, '@');
            $this->merge(['username' => $clean]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $reservedWords = [
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

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:2',
                'max:30',
                'regex:/^[a-z0-9_.-]{2,30}$/',
                'unique:artists,username',
                function ($attribute, $value, $fail) use ($reservedWords) {
                    if (in_array(strtolower($value), $reservedWords, true)) {
                        $fail('Este nome de usuário é reservado pelo sistema.');
                    }
                },
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
        return [
            'name.required' => 'O nome completo é obrigatório.',
            'name.max' => 'O nome não pode exceder 100 caracteres.',
            'username.required' => 'O nome de usuário (@) é obrigatório.',
            'username.min' => 'O nome de usuário deve ter no mínimo 2 caracteres.',
            'username.max' => 'O nome de usuário deve ter no máximo 30 caracteres.',
            'username.regex' => 'O @ deve ter entre 2 e 30 caracteres (letras, números, pontos, traços ou sublinhados).',
            'username.unique' => 'Esse @ já está em uso.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O e-mail não pode exceder 255 caracteres.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter pelo menos 6 caracteres.',
            'agree_terms.required' => 'Você precisa concordar com os termos de uso.',
            'agree_terms.accepted' => 'Você precisa concordar com os termos de uso.',
        ];
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
