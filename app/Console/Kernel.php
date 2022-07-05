<?php

namespace App\Console;

use App\Console\Commands\Bonus\UpdateUsersBonuses;
use App\Console\Commands\Cloudpayments\UpdateNotifications;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\Cloudpayments\UpdatePaymentStatus;
use App\Console\Commands\Cloudpayments\UpdateSubscription;
use App\Console\Commands\Statistics\UpdateStatistics;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // UpdatePaymentStatus::class, // Deprecated
        UpdateSubscription::class,
        UpdateNotifications::class,
        UpdateStatistics::class,
        UpdateUsersBonuses::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:clean')->dailyAt('02:50');
        $schedule->command('backup:run')->dailyAt('03:00');;
        $schedule->command('update:notifications')->everyTenMinutes();
        $schedule->command('cloudpayments:update:subscription')->hourly();
        $schedule->command('update:users_bonuses')->hourly();
        $schedule->command('update:pitech')->everyMinute();
        $schedule->command('pay:pitech')->dailyAt('01:00');
        $schedule->command('trial:pitech')->dailyAt('01:00');
        $schedule->command('update:statistics week')->everySixHours();
        $schedule->command('update:statistics month')->dailyAt('13:00');
        $schedule->command('queue:restart')
            ->everyFiveMinutes();

        $schedule->command('queue:work --daemon --queue=cp_pay')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
