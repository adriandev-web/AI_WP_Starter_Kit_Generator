#!/bin/bash

# This script was generated automatically.
# It installs and activates a list of WordPress plugins using WP-CLI.

echo "Starting plugin installation..."

echo "Installing and activating elementor..."
wp plugin install elementor --activate
echo "----------------------------------------"

echo "Installing and activating contact-form-7..."
wp plugin install contact-form-7 --activate
echo "----------------------------------------"

echo "Installing and activating yoast..."
wp plugin install yoast --activate
echo "----------------------------------------"

echo "Installing and activating wordfence..."
wp plugin install wordfence --activate
echo "----------------------------------------"

echo "All plugins have been installed and activated successfully."
