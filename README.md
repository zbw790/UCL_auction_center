# UCL Auction Center

This is the project repository for the **UCL Auction Center** coursework. The steps below will guide you on how to clone this repository directly into your `htdocs` folder and set up the project correctly in your XAMPP environment.

## Prerequisites

Make sure you have the following software installed:

- [XAMPP](https://www.apachefriends.org/index.html) for hosting the local web server and MySQL database.
- [GitHub Desktop](https://desktop.github.com/) for version control and managing GitHub repositories locally.

## Setup Steps

### Step 1: Cloning the Repository Directly to `htdocs`

1. **Open GitHub Desktop**.
2. Click on `File > Clone Repository`.
3. In the `URL` tab, enter the repository URL:

https://github.com/zbw790/UCL_auction_center.git

4. **Set the local path** to your XAMPP `htdocs` directory, making sure the project is placed in a folder named `UCL_auction_center`:

Example D:/xampp/htdocs/UCL_auction_center/

5. Click the `Clone` button to clone the repository directly into this folder.

### Step 2: Initializing the Database

1. Open your web browser and navigate to:

http://localhost/UCL_auction_center/init_db.php

2. This will run the `init_db.php` script, which initializes the MySQL database and creates all the required tables.
3. If everything works correctly, you should see a series of success messages indicating that the database was created, tables were added, and sample data was inserted.

### Step 3: Pulling and Pushing Changes

#### Pulling Changes:
1. If new updates are pushed to the repository by other members, open GitHub Desktop, go to the `UCL_auction_center` repository, and click `Fetch origin` and then `Pull origin` to download the latest changes.

#### Pushing Changes:
1. After making local changes to the project, commit your changes in GitHub Desktop, and push them to the remote repository by clicking `Push origin`.

### Troubleshooting

- If the database initialization doesn't work, make sure your XAMPP server is running, and that both **Apache** and **MySQL** services are active in the XAMPP control panel.
- Ensure the path to your `htdocs` folder is correct and matches the instructions above.

### Important Notes

- Do not push large files, especially binaries or database dumps, to the repository.
- Make sure to regularly pull the latest changes from the repository before working on new features to avoid merge conflicts.


