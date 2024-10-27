## Installation

### Prerequisites

List any software or tools required before installation (PHP 8.2, Composer 2).

### Steps

1. Clone the repository:

```bash
git clone https://github.com/chaairsoft/laravel-assignment.git
cd laravel-assignment
```

2. Install dependencies:

 ```bash
composer install
```

3. Set up the environment file:

 ```bash
cp .env.example .env
```

Edit the .env file to configure your environment settings.

4. Generate application key:

```bash
php artisan key:generate
```

5. Run migrations:

```bash
php artisan migrate
```

6. Start the server:

```bash
php artisan serve
```

### Artisan Commands

`php artisan queue:work` : Starts processing jobs on the queue as they are pushed. This command will run continuously
and process jobs in real-time. It’s important to run this command in the background to ensure your queued jobs are
handled efficiently.

Usage: Use this command when you have background tasks that need to be processed, such as sending emails or processing
images.

`php artisan products:sync` : This command manually triggers the synchronization process for products from the external
API. It fetches the latest products and updates the database accordingly.

Usage: Use this command to sync products on-demand instead of waiting for the scheduled task.

Note : This task are executed automatically every day at 12 AM.

`php artisan import:csv-products` : Imports products from a CSV file into the database. This command reads a specified
CSV file and processes each line to create or update products and their variations.

Usage: Use this command when you want to bulk import product data from an external source.

`php artisan queue:clear` : Clears all the jobs that are currently in the queue. This is useful for when you need to
reset the queue and clear any stuck or failed jobs.

Usage: Use this command with caution as it will remove all queued jobs and cannot be undone.

### CSV files location

- csv files are located in the path : `public/CSVFiles`

### Register `event listeners` and `Interfaces binding`.

In method `boot` in the path : `app/Providers/AppServiceProvider.php`


### Daily Synchronization

The project includes a scheduled task that synchronizes products from the external API every day at 12 AM.

Setting up scheduled tasks you will find them in the path : `routes/console.php`.

1. Run Task Scheduler (Local)

```bash
php artisan schedule:run
```

2. Verify Cron Jobs

To verify that the cron job is working, you can check the Laravel logs or the output of the command you've scheduled.
The logs can usually be found in `storage/logs/laravel.log` and in terminal.


### Unit Testing

I created some simple tests to ensure the data is valid.

Run all the tests in the Laravel application. This command will execute all test cases defined
in the `tests` directory, providing a summary of the results for each test.

```shell
php artisan test
```

Run only the tests in the 'ProductsServiceTest' class. 
This command uses the `--filter` option to specify that only tests within this particular class should be executed, which can be helpful
for focusing on specific functionality or isolating issues.

```shell
php artisan test --filter="ProductsServiceTest"
```

Run a specific test method named 'testValidateInteger' within the 'ProductsServiceTest' class.
This command is useful for debugging or verifying the behavior of a single test without running
the entire test suite, allowing you to quickly check the implementation of that particular method.
```shell
php artisan test --filter="ProductsServiceTest::testValidateInteger"
```

### Documentations

I made an effort to include comments in the code for clarity.
