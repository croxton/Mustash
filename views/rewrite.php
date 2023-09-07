<div class="panel">

    <div class="panel-heading">
        <div class="form-btns form-btns-top">
            <div class="title-bar title-bar--large">
                <h3 class="title-bar__title"><?php echo $cp_heading ?></h3>
            </div>
        </div>
    </div>

    <div class="panel-body">

		<?php echo ee('CP/Alert')->getAllInlines()?>

		<div class="stash_footnote" aria-expanded="true">

			<h3>Instructions</h3>

			<ol class="stash_instructions">
				<li>Create a cache directory in your <strong>public webroot</strong> and CHMOD to 777 (or CHOWN to the user PHP runs as).</li>
				<li>Set 'stash_static_basepath' to the full path of your newly created cache directory in your config.</li>
				<li>Set 'stash_static_url' to the absolute or root-relative URL of the cache directory in your config.</li>
				<li>Set 'stash_static_cache_enabled' to TRUE in your config.
				<li>Return to this page, and create or edit the <strong>.htaccess</strong> file in your public webroot by copying and pasting the code below.</li>
			</ol>

		</div>

<textarea rows="20">
<IfModule mod_rewrite.c>
 
RewriteEngine on	
RewriteBase /

#################################################################################
# START MUSTASH STATIC CACHE RULES 
<?php foreach ($sites as $site): ?>

# -------------------------------------------------------------------------------
# <?php echo $site->site_name ?>

# -------------------------------------------------------------------------------

# Exclude image files
RewriteCond $1 !\.(gif|jpe?g|png|ico)$ [NC]

# We only want GET requests
RewriteCond %{REQUEST_METHOD} GET

# Exclude CSS/ACT EE URLs and 'preview'
RewriteCond %{QUERY_STRING} !^(css|ACT|URL|preview)

# Uncomment this if you want to disable static caching for logged-in users
#RewriteCond %{HTTP_COOKIE} !exp_sessionid [NC]

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
# Remove index.php from ExpressionEngine URLs
# See: https://docs.expressionengine.com/latest/installation/best-practices.html#removing-indexphp-from-your-urls
# -------------------------------------------------------------------------------

# Removes index.php from ExpressionEngine URLs
RewriteCond %{THE_REQUEST} ^GET.*index\.php [NC]
RewriteCond %{REQUEST_URI} !/system/.* [NC]
RewriteRule (.*?)index\.php/*(.*) /$1$2 [R=301,NE,L]

# Directs all EE web requests through the site index file
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index.php?/$1 [L]

</IfModule>
</textarea>

	</div>
</div>