# Beaver Builder Lazy Loading Plugin

![beaver sleeping on water](assets/img/lazy-beaver.jpg)

The Beaver Builder Lazy Loading Plugin (BBLL) adds lazy loading functionality to [Beaver Builder](https://www.wpbeaverbuilder.com/) photo modules and row background images.

By default, Beaver Builder images are not lazy loaded. On many sites, this can be an issue for people on slow connections. BBLL does the following:

- Adds lazy loading options to the settings panels for row background images and photo modules
- Adds a new photo thumbnail size (bb-lazy-load) of 50px x 50px to be used as a placeholder
- Ensures browser compatibility by checking for the presence of `window.IntersectionObserver`
- Ensures no-js fallback by including `<noscript>` tags for each photo
- Uses only [public Beaver Builder hooks and filters](https://hooks.wpbeaverbuilder.com/bb-plugin/)

## Usage
### Local Development
**To work on this project with docker and composer**

- clone or fork the repo
- run `composer install` to install the composer dependencies. _NB - You will need your own copy of the Beaver Builder plugin._
- run `docker-compose up` to create a development environment. The project will be available at [localhost](http://localhost)
- install wordpress and activate plugins

**To work on this project without docker and composer...**
- clone or fork the repo into an existing wordpress application
- run `npm install` to install dependencies
- run `gulp watch` to watch/transpile js and sass files. processed files should be available in `/build`

## git flow
You can use [git flow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow) to aid in creating branches. Git flow encourages a branching git strategy with several types of branches
- master (for production)
- develop (pre-production)
- feature/* (new features)
- hotfix/* (bug fixes)
- release/* (tagged releases merging into master)

[You can install git flow here](https://github.com/nvie/gitflow/wiki/Installation)

If you don't want to use git flow, that's ok. Please create sensible branch names that can be pulled to `develop`

## TODO
- add lazy loading to other module types (e.g. carousels)
- try to compress placeholder images even more