# Media-Importer-Plugin
Media Importer Plugin for WordPress

- Used for importing images from upload folder "your-site.com/wp-content/uploads" to your "Media Library"
- This plugin only registers original images that are actually used in post or page content. 
- It avoids registering resized versions or unused files.

How the plugin works?

1. Parse all pages' content
2. Extract all src attributes from <img> tags
3. Strip out resized versions (-300x300, -768x1024, etc.) to get the base/original filename
4. Register only those original files if they exist in /uploads/ and aren’t yet in Media Library

How to use or install it?

- Download the "media-importer.php" file
- Upload the file to your plugins folder
- Install and activate

# Navigate the plugin options
- Go to tools section in "your-ste.com/wp-admin"
- Click on "Media Importer" option
- Click on "Run import" to import images to your media library
  
