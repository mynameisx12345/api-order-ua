CREATE TABLE likes (
 	id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
 );

 CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(1000),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    product_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    dt_created DATETIME,
    dt_modified DATETIME,
    is_removed BOOLEAN,
    dt_removed DATETIME
);