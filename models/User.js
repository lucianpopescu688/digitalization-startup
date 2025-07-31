const db = require('../config/database');
const bcrypt = require('bcryptjs');

class User {
    static async create(userData) {
        const { username, email, password, company_id, role = 'user', google_id = null } = userData;
        
        return new Promise((resolve, reject) => {
            const hashedPassword = bcrypt.hashSync(password, 10);
            
            const sql = `
                INSERT INTO users (username, email, password, company_id, role, google_id)
                VALUES (?, ?, ?, ?, ?, ?)
            `;
            
            db.run(sql, [username, email, hashedPassword, company_id, role, google_id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: this.lastID, username, email, company_id, role });
                }
            });
        });
    }

    static async findById(id) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT u.*, c.name as company_name 
                FROM users u 
                LEFT JOIN companies c ON u.company_id = c.id 
                WHERE u.id = ?
            `;
            
            db.get(sql, [id], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async findByUsername(username) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT u.*, c.name as company_name 
                FROM users u 
                LEFT JOIN companies c ON u.company_id = c.id 
                WHERE u.username = ?
            `;
            
            db.get(sql, [username], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async findByEmail(email) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT u.*, c.name as company_name 
                FROM users u 
                LEFT JOIN companies c ON u.company_id = c.id 
                WHERE u.email = ?
            `;
            
            db.get(sql, [email], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async findByGoogleId(googleId) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT u.*, c.name as company_name 
                FROM users u 
                LEFT JOIN companies c ON u.company_id = c.id 
                WHERE u.google_id = ?
            `;
            
            db.get(sql, [googleId], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async findByCompanyId(companyId) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT u.*, c.name as company_name 
                FROM users u 
                LEFT JOIN companies c ON u.company_id = c.id 
                WHERE u.company_id = ?
                ORDER BY u.created_at DESC
            `;
            
            db.all(sql, [companyId], (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    static async validatePassword(user, password) {
        return bcrypt.compareSync(password, user.password);
    }

    static async updateProfile(id, updates) {
        return new Promise((resolve, reject) => {
            const { username, email, company_id, role } = updates;
            const sql = `
                UPDATE users 
                SET username = ?, email = ?, company_id = ?, role = ?
                WHERE id = ?
            `;
            
            db.run(sql, [username, email, company_id, role, id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    static async updatePassword(id, newPassword) {
        return new Promise((resolve, reject) => {
            const hashedPassword = bcrypt.hashSync(newPassword, 10);
            const sql = 'UPDATE users SET password = ? WHERE id = ?';
            
            db.run(sql, [hashedPassword, id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }
}

module.exports = User;