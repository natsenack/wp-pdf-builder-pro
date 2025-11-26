import re

def replace_logs_in_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace console.log with PDFBuilderLogger.info for [PDF Builder] prefix
    content = re.sub(
        r'console\.log\(\s*\'\[PDF Builder\]\s*(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.info('\1'\2);",
        content,
        flags=re.DOTALL
    )

    # Replace console.error with PDFBuilderLogger.error for [PDF Builder] prefix
    content = re.sub(
        r'console\.error\(\s*\'\[PDF Builder\]\s*(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.error('\1'\2);",
        content,
        flags=re.DOTALL
    )

    # Replace console.warn with PDFBuilderLogger.warn for [PDF Builder] prefix
    content = re.sub(
        r'console\.warn\(\s*\'\[PDF Builder\]\s*(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.warn('\1'\2);",
        content,
        flags=re.DOTALL
    )

    # Replace direct console.log calls without [PDF Builder] prefix with info
    content = re.sub(
        r'console\.log\(\s*\'(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.info('\1'\2);",
        content,
        flags=re.DOTALL
    )

    # Replace direct console.error calls with error
    content = re.sub(
        r'console\.error\(\s*\'(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.error('\1'\2);",
        content,
        flags=re.DOTALL
    )

    # Replace direct console.warn calls with warn
    content = re.sub(
        r'console\.warn\(\s*\'(.*?)\'\s*,?\s*(.*?)\);',
        r"window.PDFBuilderLogger.warn('\1'\2);",
        content,
        flags=re.DOTALL
    )

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Replaced logs in {file_path}")

if __name__ == "__main__":
    replace_logs_in_file(r"i:\wp-pdf-builder-pro\plugin\assets\js\pdf-preview-integration.js")