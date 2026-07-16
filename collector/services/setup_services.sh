#!/bin/bash
set -e

echo "OPMAS systemd Services Setup Utility"
echo "-----------------------------------"

# Detect paths and user
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WORKING_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

# If run via sudo, logname/SUDO_USER gives the original user who called sudo
CURRENT_USER="${SUDO_USER:-$(whoami)}"
PYTHON_PATH="$WORKING_DIR/venv/bin/python"

# Verify virtual environment exists
if [ ! -f "$PYTHON_PATH" ]; then
    echo "ERROR: Virtual environment not found at $PYTHON_PATH"
    echo "Please set up the virtual environment first before running this script."
    exit 1
fi

echo "Detected User:      $CURRENT_USER"
echo "Working Directory:  $WORKING_DIR"
echo "Python Path:        $PYTHON_PATH"
echo ""

# Copy and compile service files using sed
echo "Compiling and copying service configurations to /etc/systemd/system/..."

sed -e "s|{{USER}}|$CURRENT_USER|g" \
    -e "s|{{WORKING_DIR}}|$WORKING_DIR|g" \
    -e "s|{{PYTHON_PATH}}|$PYTHON_PATH|g" \
    "$SCRIPT_DIR/opmas-simulator.service" | sudo tee /etc/systemd/system/opmas-simulator.service > /dev/null

sed -e "s|{{USER}}|$CURRENT_USER|g" \
    -e "s|{{WORKING_DIR}}|$WORKING_DIR|g" \
    -e "s|{{PYTHON_PATH}}|$PYTHON_PATH|g" \
    "$SCRIPT_DIR/opmas-collector.service" | sudo tee /etc/systemd/system/opmas-collector.service > /dev/null

# Reload systemd
echo "Reloading systemd manager configuration..."
sudo systemctl daemon-reload

# Enable services
echo "Enabling services to start automatically on system boot..."
sudo systemctl enable opmas-simulator
sudo systemctl enable opmas-collector

# Start services
echo "Starting services..."
sudo systemctl start opmas-simulator
sudo systemctl start opmas-collector

echo "-----------------------------------"
echo "Setup complete!"
echo "You can check status using:"
echo "  sudo systemctl status opmas-simulator"
echo "  sudo systemctl status opmas-collector"
echo ""
echo "To stop them at any time, run:"
echo "  sudo systemctl stop opmas-simulator"
echo "  sudo systemctl stop opmas-collector"
