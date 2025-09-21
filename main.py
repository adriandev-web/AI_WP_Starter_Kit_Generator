import os
from src.modules.plugin_script_generator import generate_plugin_script

def main():
    """
    Main function to run the WordPress setup script generator.
    """
    # Define paths relative to the project root
    # The script assumes it is run from the project root.
    plugins_json_path = 'templates/plugins.json'
    output_script_path = 'scripts/install_plugins.sh'

    print("Starting WordPress setup script generation...")

    # Generate the plugin installation script
    generate_plugin_script(plugins_json_path, output_script_path)

    print("\nScript generation process finished.")
    print(f"You can find the generated script at: {output_script_path}")
    print("Run it in your WordPress environment with bash.")

if __name__ == "__main__":
    main()
