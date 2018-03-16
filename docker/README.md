# Docker Setup for testing plugin

### Run setup:

    docker-compose up --build

### Backend Access:

    URL: http://localhost/backend/
    Username: styla
    Password: styla
    
### Test modular content:

The shopsystem is already setup for modular content but the cronjob is not running to update the data. It needs to be executed manually with this url:

    http://localhost/stylaapi/update
    
After this the story data on product details should show up here:

    http://localhost/food/fish/12/main-product-with-cross-selling