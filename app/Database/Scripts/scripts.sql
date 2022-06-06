CREATE TABLE likes (
 	id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
 );