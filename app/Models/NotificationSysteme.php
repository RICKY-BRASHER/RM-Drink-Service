<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSysteme extends Model
{
    protected $table = 'notifications_systeme';
    protected $primaryKey = 'id_notif';

    protected $fillable = [
        'user_id',
        'depot_id',
        'titre_n',
        'message_n',
        'icone_n',
        'couleur_n',
        'statut_lecture',
    ];

    // Relation avec l'Utilisateur (Destinataire)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_u');
    }

    // Relation avec le Dépôt
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_id', 'id');
    }
}
