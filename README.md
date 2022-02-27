Hi Everyone,

This is an assignment given by aspireApp. To run this please follow below instructions.

Prerequisite: 
1. Docker need to install with docker-compose
2. Install Git
3. Postman

1. Clone the repository
2. move inside folder
3. rename .evn.example to .env
4. Run `docker-compose up`
5. Open `http://127.0.0.1:8080/` url and login to phpmyadmin using root:root
6. Create a database named as `aspireapp`
7. Run `docker exec -it php composer install`
8. Run `docker exec -it php php artisan migrate`
9. Run `docker exec -it php php artisan db:seed`

Now setup is ready and available at http://localhost:8088. 
As I seed the application with an admin user, which is required to approve the loan request.
Credentials for admin user as follow:
email: admin@aspire.com
password: secret

Please import the postman collection to send API request.

To run phpunittest please execute below command in terminal
Run `docker exec -it php php artisan test`



