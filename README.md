# wp-cpt-import
WordPress Custom Post Types Import and Export Plugin

This plugin creates the API endpoint to generate the JSON for the website that needs export.
For example data needs to be exported from website http://abc.com

Once this plugin is intalled, it will create a following api endpoint.

http://abc.com/wp-json/v1/cptexport

Above api endpoint receives the following parameters.

ppp = Posts Per Page
cpt = Custom Post Type
pn = Page Number

On the second website, where data needs to be imported, change the URL of the second website in wp-cpt-import.php and then run the following URL.

http://xyz.com/?action=ilab_import&cpt=YOUR_CUSTOM_POST_TYPE&ppp=10&pn=0