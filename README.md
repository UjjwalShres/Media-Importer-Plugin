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
