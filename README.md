# Overview
Scandi_ChangeColorByConsole module functionality is represented by the following:
 - Creates a new console command that will create a style tag in store design field, in the database,
that will change the primary buttons colors.
 
# Instructions
The console command is `bin/magento scandiweb:color-change {color-hex} {store-id}`
This will insert the inputted color in the store.     

# Installation
## Composer Mehod
1. Run `composer require scandi/module-randomize-shipping:dev-master` in your project directory.
2. Run `bin/magento setup:upgrade`.

## Zip Method (app/code) 
Also, you can insert the module files directly in the app/code directory, in Magento 2 installation.
1. Create the app/code/Scandi/RandomizeShipping directory.
2. Insert all files from zip inside this created directory.
