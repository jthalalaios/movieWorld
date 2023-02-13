# movieWorld

1.Rename .env-example to .env (inside movieWorld-backend and on movieWorld-stack)
2.Rename docker-compose-example.yml to docker-compose.yml (inside movieWorld-stack)


Docker:

    1. Install docker: https://www.docker.com/
    2. Make sure your user belongs to docker’s group, else you have to run the commands with sudo
    3. Install docker-compose 


MovieWorld – stack:

Some info about docker-compose.yml before running the commands below.
    1. docker-compose.yml version is 3.7 and it has 3 services inside:
    • movieworld-api service, which is building from the Dockerfile on the path that it is referenced (php version 8.2-apache the latest support version till now).
    •  movieworld-postgres service, which is pulling the postgres’s image from the docker hub (latest version 15.2).
    • nginx-proxy service, which is building from the Dockerfile on the path that it is referenced , pulling the latest version from docker hub. It is very useful for firewall – rules into to 80 and 443 port numbers (HTTP & HTTPS).

Lets encrypt service is not putted into the docker-compose.yml cause I don’t have domains names to make the certifications.
Moreover, on the same path that there is the docker-compose.yml file there is a hidden file named .env and it has the environment variables to run the docker stack.
Also, there is a hidden file named .gitignore that it is used to keep away from pushing to git files or folders that we don’t want it.

Let’s get started with the movieWorld- stack:

    1. Make sure the port numbers (5439 for the outside port for postgres and the 8004 for the outside outside port of laravel are not used else the containers will not up).
    2. Create a docker’s network if it does not exists: docker network create movieworld-network
    3. Build: docker-compose build –no-cache
    4. Up the containers: docker-compose up -d
  
After docker's containers are up for the movieWorld’s stack:
    1. Go inside movieworld-api container with the following command: docker exec -it movieWorld-api bash
    2. Run composer installation: composer install
    3. Run migration: php artisan migrate
    4. In case that key is not on .env inside laravel's folder use the command: php artisan key:generate (to set the the APP_KEY value in your .env)
       
MovieWorld – api:

I have included all api calls into the: movieWorld.postman_collection.json

Credentials for postman: https://www.postman.com/

username: userpostmanapi123
password: 123test321!

After log in into postman:
    1) Go to workspaces , then Project then movieWorld collections (all api-calls are there)
    2) Below the postman user’s icon , it says “No Environment”, select the movieWorld (so the app_url dynamic will be the localhost:8004/api and the api_token (Bearer token) will be taken every time on success log in or will be destroyed on log out) 

Some info about the api:
    • User’s model uses soft deletes so when a user is deleted , user is not removed completely and on get method for movies , it wont bring this user’s movies which created by that user.
    • CRUD for User
    • CRUD for Movie
    • Post method for like/hate movie (it depends of the payload’s key, for example if the like is true then the user wants to like a movie else if it is false the user wants to remove his/her like). Same with the post method to express the hate of a movie.
    • Unprotected calls (register user and fetching-get movies). On the index -get -fetching functionalities , you can put the query parameter items to paginate the results. About the movies , it is same for the query parameter named items to paginate the results and you can order results by dates/likes/hates. It has to be on url the both query parameters (order_by, order). For example localhost:8004/api/movie?order_by=hates&order=asc 
    • The query parameter named order, it takes values: asc or desc
    • The default ordering for movies and user is order by id desc
    • Laravel’s  environment variable named APP_DEBUG is true cause the project is test project and it is not on production mode.

