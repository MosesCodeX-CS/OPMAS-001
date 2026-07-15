#!/bin/bash
set -e

echo "OPMAS systemd Services Setup Utility"
echo "-----------------------------------"

# Copy service files
echo "Copying service configuration files to /etc/systemd/system/..."
sudo cp /home/mosisto/opmas/collector/services/opmas-simulator.service /etc/systemd/system/
sudo cp /home/mosisto/opmas/collector/services/opmas-collector.service /etc/systemd/system/

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
