<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\Team;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Auth;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Dispatcher $events)
    {
        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {
            // Add some items to the menu...
            $teams = Auth::user()->teams;

            if (count($teams) < 1) {
                $teams = Team::get();
            }
            $implodeTeamIds = implode(",", $teams->pluck('id')->toArray());
            $teamIds = $teams->pluck('id')->toArray();


//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_1",
//                "text" => Notification::TYPES[Notification::TYPE_CANCEL_SUBSCRIPTION],
//                "url" => "notifications?type=1&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::TYPE_CANCEL_SUBSCRIPTION)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_2",
//                "text" => Notification::TYPES[Notification::TYPE_SUBSCRIPTION_ERRORS],
//                "url" => "notifications?type=2&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::TYPE_SUBSCRIPTION_ERRORS)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_7",
//                "text" => Notification::TYPES[Notification::WAITING_PAYMENT_CP],
//                "url" => "notifications?type=7&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::WAITING_PAYMENT_CP)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_4",
//                "classes" => 'long-title',
//                "text" => Notification::TYPES[Notification::TYPE_ENDED_SUBSCRIPTIONS_DT],
//                "url" => "notifications?type=4&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::TYPE_ENDED_SUBSCRIPTIONS_DT)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_5",
//                'classes' => 'long-title',
//                "text" => Notification::TYPES[Notification::TYPE_ENDED_SUBSCRIPTIONS_DT_3],
//                "url" => "notifications?type=5&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::TYPE_ENDED_SUBSCRIPTIONS_DT_3)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
//            $event->menu->addIn('notifications', [
//                "key" => "notification_type_6",
//                "text" => Notification::TYPES[Notification::TYPE_ENDED_TRIAL_PERIOD],
//                "url" => "notifications?type=6&processed=0&team_id={$implodeTeamIds}",
//                'label'       => Notification::where(function($query) use ($teamIds) {
//                    $query->whereIn('notifications.team_id', $teamIds)->orWhere('notifications.team_id', null);
//                })->whereType(Notification::TYPE_ENDED_TRIAL_PERIOD)->whereProcessed(false)->count(),
//                'label_color' => 'success',
//            ]);
            $event->menu->addIn('products', [
                "key" => "products_1",
                "text" => 'Подписки',
                "url" => "/products/1",
            ]);
            $event->menu->addIn('products', [
                "key" => "products_2",
                "text" => 'Разовые услуги',
                "url" => "/products/2",
            ]);
            $event->menu->addIn('statistics', [
                "key" => "statistic_type_1",
                "text" => 'Количественные',
                "url" => "statistics/quantitative",
            ]);

            $event->menu->addIn('statistics', [
                "key" => "statistic_type_2",
                "text" => 'Финансовые',
                "url" => "statistics/financial",
            ]);

            $event->menu->addIn('stat-reports', [
                "key" => "report_3",
                "text" => 'Отказы (прямой перевод)',
                "url" => "reports/get-reports/3",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_8",
                "text" => 'Заканчиваются (прямой пер.)',
                "url" => "reports/get-reports/8",
            ]);
            $event->menu->addIn('stat-reports', [
                "key" => "report_4",
                "text" => 'Отмененные подписки',
                "url" => "reports/get-reports/4",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_5",
                "text" => 'Жду оплату (клиенты)',
                "url" => "reports/get-reports/5",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_13",
                "text" => 'Пробники',
                "url" => "reports/get-reports/13",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_6",
                "text" => 'Просрочка (пробники)',
                "url" => "reports/get-reports/6",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_9",
                "text" => 'Ошибки оплат',
                "url" => "reports/get-reports/9",
            ]);
            $event->menu->addIn('reports', [
                "key" => "report_9",
                "text" => 'Возараты',
                "url" => "reports/get-reports/14",
            ]);
            $event->menu->addIn('stat-reports', [
                "key" => "report_10",
                "text" => 'Должники',
                "url" => "reports/get-reports/10",
            ]);

            $event->menu->addIn('stat-reports', [
                "key" => "userlogs",
                "text" => 'Логи действий',
                "url" => "/userlogs",
            ]);
//            $event->menu->addIn('reports', [
//                "key" => "report_11",
//                "text" => 'Мои бонусы',
//                "url" => "/reports/operator-bonus-details/".Auth::id(),
//            ]);
//            $event->menu->addIn('reports', [
//                "key" => "report_10",
//                "text" => 'Бонусы операторов',
//                "url" => "reports/get-reports/11",
//                    'icon' => 'fa fa-user-check',
//                    "can"  => [
//                        // 'can-operator',
//                        'can-head',
//                        'can-host',
//                    ],
//            ]);
            // $event->menu->addIn('statistics', [
            //     "key" => "statistic_type_3",
            //     "text" => 'Итоговые',
            //     "url" => "statistics/total",
            // ]);
        });
    }

    private function registerServices()
    {
    }
}
