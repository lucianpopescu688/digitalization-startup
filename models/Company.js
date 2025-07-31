const db = require('../config/database');

class Company {
    static async create(name) {
        return new Promise((resolve, reject) => {
            const sql = 'INSERT INTO companies (name) VALUES (?)';
            
            db.run(sql, [name], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: this.lastID, name });
                }
            });
        });
    }

    static async findById(id) {
        return new Promise((resolve, reject) => {
            const sql = 'SELECT * FROM companies WHERE id = ?';
            
            db.get(sql, [id], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async findByName(name) {
        return new Promise((resolve, reject) => {
            const sql = 'SELECT * FROM companies WHERE name = ?';
            
            db.get(sql, [name], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    static async getAll() {
        return new Promise((resolve, reject) => {
            const sql = 'SELECT * FROM companies ORDER BY name ASC';
            
            db.all(sql, [], (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    static async findOrCreate(name) {
        try {
            let company = await this.findByName(name);
            if (!company) {
                company = await this.create(name);
            }
            return company;
        } catch (error) {
            throw error;
        }
    }

    static async getUserCount(companyId) {
        return new Promise((resolve, reject) => {
            const sql = 'SELECT COUNT(*) as count FROM users WHERE company_id = ?';
            
            db.get(sql, [companyId], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row.count);
                }
            });
        });
    }

    static async getVideoCount(companyId) {
        return new Promise((resolve, reject) => {
            const sql = 'SELECT COUNT(*) as count FROM videos WHERE company_id = ?';
            
            db.get(sql, [companyId], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row.count);
                }
            });
        });
    }
}

module.exports = Company;