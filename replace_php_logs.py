import re
import os

def replace_error_logs_in_php_files(directory):
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                file_path = os.path.join(root, file)
                try:
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()

                    # Replace error_log('PDF_BUILDER_DEBUG: ...') with $this->debug_log('...') in class context
                    # First check if it's in a class method
                    if 'class ' in content and 'function ' in content:
                        content = re.sub(
                            r'error_log\(\s*\'PDF_BUILDER_DEBUG:\s*(.*?)\'\s*\);',
                            r'$this->debug_log(\'\1\');',
                            content,
                            flags=re.DOTALL
                        )

                    # Replace error_log('PDF_BUILDER_DEBUG: ...') with debug_log('...') for static calls
                    content = re.sub(
                        r'error_log\(\s*\'PDF_BUILDER_DEBUG:\s*(.*?)\'\s*\);',
                        r'debug_log(\'\1\');',
                        content,
                        flags=re.DOTALL
                    )

                    # Also replace other error_log calls that might be debug-related
                    content = re.sub(
                        r'error_log\(\s*\'PDF_Builder.*?:\s*(.*?)\'\s*\);',
                        r'debug_log(\'\1\');',
                        content,
                        flags=re.DOTALL
                    )

                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(content)

                    print(f"Processed {file_path}")

                except Exception as e:
                    print(f"Error processing {file_path}: {e}")

if __name__ == "__main__":
    replace_error_logs_in_php_files(r"i:\wp-pdf-builder-pro\plugin")