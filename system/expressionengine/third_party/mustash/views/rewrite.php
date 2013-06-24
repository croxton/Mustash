<h3>To set up static caching:</h3>

<ol class="stash_instructions">
	<li>Create a cache directory in your <strong>public webroot</strong> and CHMOD to 777 (or CHOWN to the user PHP runs as).</li>
	<li>Set 'stash_static_basepath' to the full path of your newly created cache directory in your config.</li>
	<li>Set 'stash_static_url' to the absolute or root-relative URL of the cache directory in your config.</li>
	<li>Set 'stash_static_cache_enabled' to TRUE in your config.
	<li>Return to this page, and create or edit the <strong>.htaccess</strong> file in your public webroot by copying and pasting the code below.</li>
	<li>Note that the code generated below is specific to your server enviroment.
	 <br>You will need to create an individual .htaccess file for each environment that you deploy to.</li>
</ol>

<textarea rows="20" class="stash_code_format">
<IfModule mod_rewrite.c>
 
RewriteEngine on	

#################################################################################
# START MUSTASH STATIC CACHE RULES 
<?php foreach ($sites as $site): ?>

# -------------------------------------------------------------------------------
# <?php echo $site->site_name ?>

# -------------------------------------------------------------------------------

# Exclude POST requests
RewriteCond %{REQUEST_METHOD} !=POST

# Exclude CSS/ACT EE URLs
RewriteCond %{QUERY_STRING} !^(css|ACT|URL)

# Remove index.php from conditions
RewriteCond $1 ^(index.php/)*(.*)(/*)$

# Check if cached index.html exists
RewriteCond <?php echo $cache_path?><?php echo $site->site_id?>/$2/index.html (.*\.(.*))$
RewriteCond %1 -f

# Rewrite to the cached page
RewriteRule ^(index.php/*)*(.*)(/*) <?php echo $cache_url?><?php echo $site->site_id?>/$2/index.%2 [L]

<?php endforeach; ?>
# END MUSTASH STATIC CACHE RULES
#################################################################################

# -------------------------------------------------------------------------------
# Officially supported method to remove index.php from ExpressionEngine URLs
# See: http://ellislab.com/expressionengine/user-guide/urls/remove_index.php.html
# -------------------------------------------------------------------------------

RewriteCond $1 !\.(gif|jpe?g|png)$ [NC]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php/$1 [L]

</IfModule>

</textarea>