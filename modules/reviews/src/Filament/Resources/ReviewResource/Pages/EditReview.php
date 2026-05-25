<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReview extends EditRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Схвалити')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== Review::STATUS_APPROVED)
                ->action(function (): void {
                    $this->record->approve();
                    Notification::make()->success()->title('Відгук схвалено')->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('reject')
                ->label('Відхилити')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== Review::STATUS_REJECTED)
                ->action(function (): void {
                    $this->record->reject();
                    Notification::make()->success()->title('Відгук відхилено')->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // If admin_reply was just set/changed, update the timestamp
        if ($record->wasChanged('admin_reply') && ! empty($record->admin_reply)) {
            $record->update(['admin_replied_at' => now()]);
        }

        // Recalculate product rating when status changes
        if ($record->wasChanged('status')) {
            $record->product?->updateRatingFromReviews();
        }
    }
}
