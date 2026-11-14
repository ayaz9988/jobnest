<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Models\Resume;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('resume_id', null))
                    ->required(),
                Select::make('job_listing_id')
                    ->relationship('jobListing', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('resume_id')
                    ->label('Resume')
                    ->relationship('resume','file_name',
                        fn (Builder $query, Get $get) => $query
                            ->when(
                                filled($get('user_id')),
                                fn (Builder $query) => $query->where('user_id', $get('user_id')),
                                fn (Builder $query) => $query->whereRaw('1 = 0'),
                            ),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Resume $record) => $record->downloadName())
                    ->searchable(['file_name'])
                    ->preload()
                    ->disabled(fn (Get $get) => blank($get('user_id')))
                    ->helperText('Choose an applicant first.')
                    ->rule(fn (Get $get) => Rule::exists('resumes', 'id')
                        ->where('user_id', $get('user_id'))),
                Select::make('status')
                    ->options([
                        'applied' => 'Applied',
                        'reviewed' => 'Reviewed',
                        'interview' => 'Interview',
                        'withdraw' => 'Withdraw',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ])
                    ->default('applied')
                    ->required(),
                Textarea::make('cover_letter')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('applied_at')
                    ->required()
                    ->default(now()),
            ]);
    }
}
