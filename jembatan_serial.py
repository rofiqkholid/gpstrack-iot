import serial
import requests
import json
import time

# --- KONFIGURASI --- #
# Ganti 'COM4' dengan port ESP32 Anda yang sebenarnya (misal: COM4, COM5)
SERIAL_PORT = 'COM4'     
BAUD_RATE   = 115200     
API_URL     = 'http://127.0.0.1:8000/api/gps' 

def main():
    print(f"Mencoba terhubung ke {SERIAL_PORT} dengan baud rate {BAUD_RATE}...")
    try:
        # Membuka koneksi serial
        ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=1)
        print(f"Berhasil terhubung ke {SERIAL_PORT}!")
        print("Menunggu data dari ESP32...\n")

        last_sent = 0
        while True:
            # 1. Baca data dari ESP jika ada log/tulisan masuk
            if ser.in_waiting > 0:
                line = ser.readline().decode('utf-8', errors='ignore').strip()
                if line:
                    print(f"Menerima log dari ESP32: {line}")
            
            # 2. Kirim data Ping 'Online' setiap 5 detik SELAMA kabel tertancap & serial sukses
            current_time = time.time()
            if current_time - last_sent >= 5.0:
                data = {
                    "device_id": "ESP32-GPS-01",
                    "latitude": -6.200000, 
                    "longitude": 106.816666,
                    "speed": 0
                }
                
                try:
                    response = requests.post(API_URL, json=data)
                    print(f"[{time.strftime('%H:%M:%S')}] -> Status Online dikirim ke Web! Respon: {response.status_code}")
                    last_sent = current_time
                except requests.exceptions.ConnectionError:
                    print(f"[{time.strftime('%H:%M:%S')}] Error: Tidak terhubung ke Laravel di {API_URL}")
                    
            time.sleep(0.1) # Jeda kecil untuk beban CPU
    except serial.SerialException as e:
        print(f"\nError membuka port serial: {e}")
        print(f"Pastikan port {SERIAL_PORT} benar dan tidak sedang digunakan oleh aplikasi lain (seperti Serial Monitor di IDE).")
    except KeyboardInterrupt:
        print("\nProgram dihentikan oleh pengguna.")
        if 'ser' in locals() and ser.is_open:
            ser.close()

if __name__ == '__main__':
    main()
