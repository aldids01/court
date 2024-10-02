<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items():HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    protected static function booted()
    {
        static::created(function ($order) {
            $filledby = $order->user->name;
            $users = User::all();

            // Send notification to all users when a new order is created
            foreach ($users as $user) {
                Notification::make()
                    ->title('New Form was filled.')
                    ->success()
                    ->body($filledby.'  filled some form for your action Sir.' )
                    ->actions([
                        Action::make('view')
                            ->label('View Form')
                            ->url('/admin/orders/'.$order->id)
                            ->button(),
                    ])
                    ->sendToDatabase($user); // Send notification to each user
            }
        });
    }
}
