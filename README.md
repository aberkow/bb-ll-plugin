# WP Plugin Development Boilerplate 
This repo is a boilerplate for doing quick localdev environments managed with composer and docker.
By default it will create images to install wordpress/mysql and run gulp (or webpack).

Some dependencies may need others to work together. For instance the UCToday castor module needs the UCToday plugin. For a full list of our private plugins and themes, [please see our satis repo](https://packages.ucdev.net/).

## git flow
We use [git flow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow). Git flow encourages a branching git strategy with several types of branches
- master (for production)
- develop (pre-production)
- feature/* (new features)
- hotfix/* (bug fixes)
- release/* (tagged releases merging into master)

[You can install git flow here](https://github.com/nvie/gitflow/wiki/Installation)

### Typical development workflow starting
- create a new project using composer
  - `composer create-project ucomm/wp-plugin-project-boilerplate <new-plugin-name>`
  - change the `name` and `description` of the project in the composer.json file. The name is especially important because it is the name that will be used to `require` this project using composer.
- `git flow init -d`
- create a new bitbucket repo under the ucomm team account
- `git flow feature start {feature_name}` this starts a new branch
- work on stuff...
- `git flow feature finish {feature_name}` this merges the branch into `develop`
- test the `develop` branch and share it with others for approval

**See below for instructions on creating hooks for jenkins and bitbucket pipelines for automatic deployment/package management**

### Creating releases
Tags **must** follow the [semver system](http://semver.org/). Follow these steps to complete a release
- on the `develop` branch
  - check `git tag` for the most recent version number
  - bump the version number in `./{plugin-name}.php`
  - add changelog notes to `./changelog.md`
- commit these changes (usually with something like `git -m 'version bump'`)
- follow the process below to create a new tagged release.
```bash
$ git flow release start {new_tag}
$ git flow release finish {new_tag}
$ # in the first screen add new info or just save the commit message as is
$ # in the second screen type in the same tag you just used and save.
$ git push --tags && git push origin master
$ git checkout develop
``` 

## Usage
### Getting started

This project is intended to get development started quickly. As you develop your individual project, please update this readme with project specific details. If you want to use webpack or parcel instead of gulp. That's completely fine, you simply need to add documentation. Also please update the changelog with notes about new versions.

NB - If you anticipate needing to use a plugin which needs access to the `vendor` directory (like Castor), map it as follow. In `docker-compose.yml`, find the `web` image and under `volumes` write `- ./vendor:/var/www/html/content/plugins/{plugin_name}/vendor`. This gives both the current theme and plugin the vendor folder.

### To get a project running.
There are two entrypoint scripts in `./.entrypoint`. Make sure they are executable by running `chmod +x ./.entrypoint/{script_name}.sh`. The server script (does something I don't understand - AB). The local dev script will install packages in `./package.json` and then run `gulp watch`. The script can be changed as needed to perform other tasks before or after gulp runs.
```bash
$ composer install # <- first time only
$ docker-compose up
```
You may notice that `node_modules` and built assets are not present on your host. That's ok. They're inside the local-dev container. This prevents an issue with cross platform npm packages.

### Local asset development
This boilerplate comes with a docker service to handle js and css development. When you run `docker-compose up` the local service will start and run tasks defined in the container's [gulpfile.js](https://bitbucket.org/ucomm/docker-images/src/master/gulp-4/gulpfile.js). The built in gulpfile can be overridden by binding a local gulpfile to the container.

There are two required volume mounts.

```
version: '3.7'
services:
  local:
    volumes:
      - ./src:/project/src
      - build:/project/build

# a shared docker volume
volumes:
  build:
```

**NB - the build volume only exists within the docker network during local development. This allows the local container to share files with the server.**

The container also includes other files that can be overridden optionally/as needed by binding them to the container. 
- [`.babelrc`](https://babeljs.io/docs/en/6.26.3/babelrc) and [`.browserslistsrc`](https://github.com/browserslist/browserslist) files for configuration
- `package.json` for js dependencies
- `webpack.common.js` handles [environment agnostic webpack config](https://webpack.js.org/guides/production/)
- `webpack.dev.js` handles dev environment config
- `webpack.prod.js` handles prod environment config

```
version: '3.7'
services:
  local:
    volumes:
      - ./gulpfile.js:/project/gulpfile.js
```

### Viewing a project.
This project will be available at [localhost](http://localhost) and has live reloading via [browsersync](https://www.browsersync.io/) for development at [localhost:3000](http://localhost:3000).

CSS and JS assets are shared between the local-dev container and web server. Please see the docker-compose file for how they are mounted/shared.

### Accessing containers
To access a particular docker container, find the container name and then enter an interactive terminal.
```bash
$ docker ps # to get the container name
$ docker exec -it container_name bash
```
### Debugging Wordpress
Wordpress debug logs can be found inside the web container at `/etc/httpd/logs/error_log`

## Bitbucket
### Creating releases
Assuming you're using git flow, tag the release with the command `git flow release start {version_number}`. Tags must follow the [semver system](http://semver.org/). Follow these steps to complete a release
```bash
$ git tag # check the current tags/versions on the project
$ git flow release start {new_tag}
$ git flow release finish {new_tag}
$ # in the first screen add new info or just save the commit message as is
$ # in the second screen type in the same tag you just used and save.
$ git push --tags && git push origin master
$ git checkout develop
``` 
Finally re-run the pipeline build on the [satis repo](https://bitbucket.org/ucomm/composer-repository).
### Pipelines
This repo has a bitbucket pipelines (written by Adam Berkowitz) attached in case you wish to create zip file downloads for tags/branches. **You may exclude files/folders from the zip archive by adding them to composer.json. The syntax is the same as gitignore. You may explicitly include files/directories as well (e.g. !.gitignore, !vendor/*).** To enable pipelines on bitbucket, simply visit the project repo and enable pipelines at `repo -> settings -> Pipelines/settings`.

### Access Keys
Many projects require integration with either Jenkins (below), or our Satis/composer package repo. These require ssh keys. To add keys to a project...
- Find another project with keys (castor is a good example)
- In that project, go to `Settings -> General -> Access Keys` 
- Copy the keys you need
- In the project you're working on, add new keys (typically called Jenkins and Composer for clarity)

## Jenkins

### Building
When you're ready to build to production, make sure to set `process.env.NODE_ENV = 'production'`. This will allow the gulpfile and webpack to properly minify js assets and reduce file size.

### Automating builds
In order to ensure automatic pushes to our development server(s) take the following steps
- Create a new [Jenkins project](http://ci.pr.uconn.edu:8080/) (either from scratch or by copying - see the [Castor project](http://ci.pr.uconn.edu:8080/job/Castor%20-%20Push%20to%20Dev%20(Aurora%20Sandbox)/) for an example)
- In bitbucket settings, go to `Settings -> Workflow -> Webhooks` and add a hook to Jenkins at the url `http://ci.pr.uconn.edu:8080/bitbucket-hook/`.
- In bitbucket settings, go to `Settings -> General -> Access Keys` and add the Jenkins ssh key. The key can be copy/pasted from another repo
- **If this project is going to be deployed to either the Aurora sandbox or health dev servers, make a new directory with the same name as the project using an ftp client. Otherwise the deployment may fail.**

## Satis
To create a consumable package for other projects, this project needs to be added to our [satis repo](https://bitbucket.org/ucomm/composer-repository). The repo is rebuilt nightly using a Jenkins task. Please see that repo for instructions on adding projects.

## Using wp-project-boilerplate for a plugin

It would be a good idea to keep UComm/WordPress dependencies in require-dev, and only keep functional dependencies in "require".  That way, your package will export with only the required files for your plugin/theme to function, and not include a full WP install. See the [Castor plugin](https://bitbucket.org/ucomm/castor/src) for an example that uses both.

## Known Issues
- The Docker images don't currently work under Linux. This is an issue related to ip tables.
