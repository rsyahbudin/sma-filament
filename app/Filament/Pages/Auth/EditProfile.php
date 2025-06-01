<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentParent;

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

        // Tambahkan form orang tua jika user adalah siswa
        if (Auth::user() && Auth::user()->role && strtolower(Auth::user()->role->name) === 'student') {
            $fields[] = Repeater::make('parents')
                ->relationship()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->maxLength(255),
                    TextInput::make('occupation')
                        ->maxLength(255),
                    Select::make('type')
                        ->options([
                            'father' => 'Father',
                            'mother' => 'Mother',
                            'guardian' => 'Guardian',
                        ])
                        ->required(),
                ])
                ->columns(2)
                ->minItems(1)
                ->maxItems(3)
                ->defaultItems(1)
                ->label('Parent Information');
        }

        return $form->schema($fields);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika user adalah siswa, pastikan data orang tua disimpan dengan benar
        if (Auth::user() && Auth::user()->role && strtolower(Auth::user()->role->name) === 'student') {
            // Hapus data orang tua dari array utama karena akan ditangani oleh relationship
            unset($data['parents']);
        }

        return $data;
    }
}
