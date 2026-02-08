import requests
import time
import random
import sys

# Configuration
API_URL = "http://localhost:8000/api/gps"
DEVICES = ["DEVICE_001", "DEVICE_002", "DEVICE_003"]
DELAY = 2  # Seconds

def generate_movement(lat, lng):
    # Move randomly within ~100 meters
    lat += random.uniform(-0.001, 0.001)
    lng += random.uniform(-0.001, 0.001)
    return lat, lng

def main():
    print(f"Starting GPS Simulation to {API_URL}")
    print("Press Ctrl+C to stop")
    
    # Initial positions (Around Kecamatan Pebayuran, Kab Bekasi)
    # Center: -6.20695, 107.29205
    positions = {
        "DEVICE_001": (-6.20695, 107.29205),
        "DEVICE_002": (-6.20500, 107.29500),
        "DEVICE_003": (-6.20800, 107.29000),
    }

    try:
        while True:
            for device_id in DEVICES:
                lat, lng = positions[device_id]
                lat, lng = generate_movement(lat, lng)
                positions[device_id] = (lat, lng)
                
                speed = random.uniform(0, 60)
                
                payload = {
                    "device_id": device_id,
                    "latitude": lat,
                    "longitude": lng,
                    "speed": round(speed, 2)
                }
                
                try:
                    response = requests.post(API_URL, json=payload)
                    if response.status_code == 201:
                        print(f"[{device_id}] Sent: {lat:.6f}, {lng:.6f} | Speed: {speed:.1f} km/h")
                    else:
                        print(f"[{device_id}] Failed: {response.status_code} - {response.text}")
                except Exception as e:
                    print(f"[{device_id}] Error: {e}")
            
            time.sleep(DELAY)

    except KeyboardInterrupt:
        print("\nSimulation stopped.")

if __name__ == "__main__":
    main()
