import json
import os

def generate_plugin_script(plugins_json_path, output_script_path):
    """
    Generates a bash script to install and activate WordPress plugins.

    Args:
        plugins_json_path (str): The path to the JSON file containing the list of plugins.
        output_script_path (str): The path where the generated script will be saved.
    """
    try:
        with open(plugins_json_path, 'r') as f:
            plugins = json.load(f)
    except FileNotFoundError:
        print(f"Error: The file {plugins_json_path} was not found.")
        return
    except json.JSONDecodeError:
        print(f"Error: Could not decode JSON from {plugins_json_path}.")
        return

    script_content = "#!/bin/bash\n\n"
    script_content += "# This script was generated automatically.\n"
    script_content += "# It installs and activates a list of WordPress plugins using WP-CLI.\n\n"
    script_content += 'echo "Starting plugin installation..."\n\n'

    for plugin in plugins:
        script_content += f'echo "Installing and activating {plugin}..."\n'
        script_content += f"wp plugin install {plugin} --activate\n"
        script_content += 'echo "----------------------------------------"\n\n'

    script_content += 'echo "All plugins have been installed and activated successfully."\n'

    try:
        # Ensure the output directory exists
        output_dir = os.path.dirname(output_script_path)
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)

        with open(output_script_path, 'w') as f:
            f.write(script_content)

        # Make the script executable
        os.chmod(output_script_path, 0o755)

        print(f"Script generated successfully at {output_script_path}")

    except IOError as e:
        print(f"Error writing to file {output_script_path}: {e}")

if __name__ == '__main__':
    # This part is for direct execution and testing of the module.
    # In the final application, this module will be called from main.py.

    # Assuming the script is run from the root of the project
    project_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    plugins_file = os.path.join(project_root, 'templates', 'plugins.json')
    output_file = os.path.join(project_root, 'scripts', 'install_plugins.sh')

    # For the agent's environment, let's use relative paths
    plugins_file = 'templates/plugins.json'
    output_file = 'scripts/install_plugins.sh'

    generate_plugin_script(plugins_file, output_file)
