<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        $fields = [];
        // Tampilkan NIS hanya jika user student
        if (Auth::user() && Auth::user()->role && strtolower(Auth::user()->role->name) === 'student') {
            $fields[] = TextInput::make('nis')
                ->label('NIS')
                ->disabled();
        }
        $fields = array_merge($fields, [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            TextInput::make('phone')->tel()->maxLength(255),
            TextInput::make('address')->maxLength(255),
            TextInput::make('date_of_birth')->label('Date of Birth')->type('date'),
            Select::make('gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                ])
                ->label('Gender'),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
        return $form->schema($fields);
    }
}
