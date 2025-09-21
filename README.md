# WordPress Setup Automation

This project provides a set of tools to automate the setup and configuration of WordPress websites. It allows for quick generation of installation scripts for plugins, configuration of themes, and more.

## Features

-   **Plugin Script Generation**: Automatically generate a bash script to install and activate a list of WordPress plugins.
-   **Customizable**: Easily customize the list of plugins by editing a JSON file.
-   **Modular**: The project is structured in modules for easy extension.

## How to use

1.  **Configure Plugins**: Open the `templates/plugins.json` file and add or remove the WordPress plugin slugs you want to install.
2.  **Generate the script**: Run the main script from the project root:
    ```bash
    python main.py
    ```
3.  **Run the generated script**: The script will generate a file at `scripts/install_plugins.sh`. You need to run this script in your WordPress environment where WP-CLI is installed.
    ```bash
    bash scripts/install_plugins.sh
    ```

## Project Structure

-   `main.py`: The main entry point of the application.
-   `src/`: Contains the source code of the project.
    -   `src/modules/`: Contains the different modules of the application (e.g., plugin script generator).
-   `templates/`: Contains configuration templates.
    -   `templates/plugins.json`: The list of plugins to install.
-   `scripts/`: Contains the generated scripts.
-   `docs/`: For documentation files.

## Future development

This is the first version of the tool. Future developments will include:
-   Theme configuration (colors, fonts).
-   Page structure creation.
-   Integration with AI for content generation.
-   Integration with n8n for workflow automation.