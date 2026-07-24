<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\View; // 💡 Import indispensable
use Illuminate\Support\Facades\Auth; // 💡 Import indispensable

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 INJECTEUR CENTRALISÉ DOUBLE-FLUX POUR TOUTES LES PAGES BLADE
        View::composer('*', function ($view) {
            
            if (Auth::check()) {
                $user = Auth::user();
                
                $notificationsAlertes = collect();
                $notificationsMessages = collect();

                // ----------------================================================
                // FLUX N°1 : LES ALERTES LOGISTIQUES DE STOCKS (TABLE alertes_stocks)
                // ----------------------------------------------------------------
                if (Schema::hasTable('alertes_stocks') && $user->role_u !== 'client') {
                    if ($user->role_u === 'admin') {
                        $notificationsAlertes = DB::table('alertes_stocks')
                            ->join('produits', 'alertes_stocks.produit_id', '=', 'produits.id_produit')
                            ->join('depots', 'alertes_stocks.depot_id', '=', 'depots.id')
                            ->where('statut_lecture', 'non_lu')
                            ->select('alertes_stocks.*', 'produits.nom_p', 'produits.format_p', 'depots.nom_depot')
                            ->orderBy('alertes_stocks.created_at', 'desc')
                            ->get();
                    } else {
                        $notificationsAlertes = DB::table('alertes_stocks')
                            ->join('produits', 'alertes_stocks.produit_id', '=', 'produits.id_produit')
                            ->where('alertes_stocks.depot_id', $user->depot_id)
                            ->where('statut_lecture', 'non_lu')
                            ->select('alertes_stocks.*', 'produits.nom_p', 'produits.format_p')
                            ->orderBy('alertes_stocks.created_at', 'desc')
                            ->get();
                    }
                }

                // ----------------================================================
                // FLUX N°2 : LES MESSAGES & COMMANDES (TABLE notifications_systeme)
                // ----------------------------------------------------------------
                if (Schema::hasTable('notifications_systeme')) {
                    if ($user->role_u === 'admin') {
                        $notificationsMessages = DB::table('notifications_systeme')
                            ->where('statut_lecture', 'non_lu')
                            ->orderBy('created_at', 'desc')
                            ->get();
                    } elseif ($user->role_u === 'employe') {
                        $notificationsMessages = DB::table('notifications_systeme')
                            ->where('depot_id', $user->depot_id)
                            ->whereIn('user_id', [$user->id_u, null])
                            ->where('statut_lecture', 'non_lu')
                            ->orderBy('created_at', 'desc')
                            ->get();
                    } else {
                        $notificationsMessages = DB::table('notifications_systeme')
                            ->where('user_id', $user->id_u)
                            ->where('statut_lecture', 'non_lu')
                            ->orderBy('created_at', 'desc')
                            ->get();
                    }
                }

                // Envoi des deux flux indépendants aux structures HTML
                $view->with('notificationsAlertes', $notificationsAlertes);
                $view->with('notificationsMessages', $notificationsMessages);

            } else {
                $view->with('notificationsAlertes', collect());
                $view->with('notificationsMessages', collect());
            }
        });
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
