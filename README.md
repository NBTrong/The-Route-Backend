# Backend Docker for The Router Project

Docker clean Laravel 8 installation with PostgreSQL, Redis and nginx

# Cài đặt

## WSL2

1. Tải về gói update Linux kernel qua link https://wslstorestorage.blob.core.windows.net/wslblob/wsl_update_x64.msi

2. Để WSL 2 làm mặc định khi cài đặt Linux distribution, chạy lệnh sau: `wsl --set-default-version 2`

3. Về cơ bản làm xong bước trên là ok rồi nhưng nếu bạn muốn dùng Ubuntu hay Kali hay Debian thì vào Microsoft Store để tải các Linux distribution về nhé (https://aka.ms/wslstore)

4. Sau đó Linux sẽ đòi hỏi bạn tạo Username, Password các kiểu nhưng chỉ một lần thôi

Nếu bạn dùng WSL 2 làm backend thì không cần lo việc thay đổi Settings của Docker Desktop như tài nguyên máy cần sử dụng hay mount đường dẫn trong/ngoài container bởi mọi thứ đã được config sẵn rồi.

## Docker 

Trên Windows, cài Docker Desktop Installer theo link này: https://www.docker.com/products/docker-desktop.

## Setup môi trường cho backend

1. Chạy lệnh sau: `git clone https://github.com/quanganhquanganh/The-Route-Backend.git`

2. `cd The-Route-Backend`

3. Setup file .env ( `cd src` sau đó `cp .env.example .env` xong rồi `cd ..`) 

3. `docker-compose up -d --build`

4. `docker-compose run --rm composer install`

5. `docker-compose run --rm artisan migrate --seed` 

6. Vào localhost:8080 để check đã được chưa.

## Kết nối với pgAdmin4

1. Mở pgAdmin4, chọn Add Server, sau đó điền Name:

![image](https://user-images.githubusercontent.com/59202082/140951787-281146fc-f649-4788-bc81-7b66b330aa83.png)

2. Tiếp theo mở Connection:

![image](https://user-images.githubusercontent.com/59202082/140951900-31395317-e553-4308-89bc-1c23174b376d.png)

3. Mở WSL2 Terminal ra (tùy Linux Distribution bạn chọn, ex: Debian)

4. Chạy lệnh sau: `ip addr`, sau đó thử các link inet ipv4 trong đó. (Trường hợp của tôi là 172.28.73.119)

![image](https://user-images.githubusercontent.com/59202082/140951236-583f563d-224e-48f1-a985-be744f4d9e0a.png)

5. Quay lại Connection và thử link ip lấy được cho vào Host name/address, Password để postgres và các thông tin còn lại điền như dưới:

![image](https://user-images.githubusercontent.com/59202082/140952470-5e6f0a0e-4ab6-4294-94a0-0a377b1c8a94.png)

# Các vấn đề có thể gặp phải
