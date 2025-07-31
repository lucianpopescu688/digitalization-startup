const db = require('../config/database');

class Video {
    static async create(videoData) {
        const { 
            filename, 
            original_name, 
            title, 
            description, 
            tags, 
            file_path, 
            file_size, 
            mime_type, 
            duration, 
            user_id, 
            company_id 
        } = videoData;
        
        return new Promise((resolve, reject) => {
            const sql = `
                INSERT INTO videos (
                    filename, original_name, title, description, tags, 
                    file_path, file_size, mime_type, duration, user_id, company_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `;
            
            db.run(sql, [
                filename, original_name, title, description, tags,
                file_path, file_size, mime_type, duration, user_id, company_id
            ], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: this.lastID, ...videoData });
                }
            });
        });
    }

    static async findById(id) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT v.*, u.username, c.name as company_name
                FROM videos v 
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN companies c ON v.company_id = c.id
                WHERE v.id = ?
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

    static async findByUserId(userId) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT v.*, u.username, c.name as company_name
                FROM videos v 
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN companies c ON v.company_id = c.id
                WHERE v.user_id = ?
                ORDER BY v.upload_date DESC
            `;
            
            db.all(sql, [userId], (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    static async findByCompanyId(companyId) {
        return new Promise((resolve, reject) => {
            const sql = `
                SELECT v.*, u.username, c.name as company_name
                FROM videos v 
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN companies c ON v.company_id = c.id
                WHERE v.company_id = ?
                ORDER BY v.upload_date DESC
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

    static async getAll(limit = null, offset = 0) {
        return new Promise((resolve, reject) => {
            let sql = `
                SELECT v.*, u.username, c.name as company_name
                FROM videos v 
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN companies c ON v.company_id = c.id
                ORDER BY v.upload_date DESC
            `;
            
            let params = [];
            if (limit) {
                sql += ' LIMIT ? OFFSET ?';
                params = [limit, offset];
            }
            
            db.all(sql, params, (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    static async update(id, updates) {
        const { title, description, tags } = updates;
        
        return new Promise((resolve, reject) => {
            const sql = `
                UPDATE videos 
                SET title = ?, description = ?, tags = ?
                WHERE id = ?
            `;
            
            db.run(sql, [title, description, tags, id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    static async delete(id) {
        return new Promise((resolve, reject) => {
            const sql = 'DELETE FROM videos WHERE id = ?';
            
            db.run(sql, [id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    static async search(query, companyId = null) {
        return new Promise((resolve, reject) => {
            let sql = `
                SELECT v.*, u.username, c.name as company_name
                FROM videos v 
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN companies c ON v.company_id = c.id
                WHERE (v.title LIKE ? OR v.description LIKE ? OR v.tags LIKE ?)
            `;
            
            let params = [`%${query}%`, `%${query}%`, `%${query}%`];
            
            if (companyId) {
                sql += ' AND v.company_id = ?';
                params.push(companyId);
            }
            
            sql += ' ORDER BY v.upload_date DESC';
            
            db.all(sql, params, (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    static async getCount(companyId = null) {
        return new Promise((resolve, reject) => {
            let sql = 'SELECT COUNT(*) as count FROM videos';
            let params = [];
            
            if (companyId) {
                sql += ' WHERE company_id = ?';
                params.push(companyId);
            }
            
            db.get(sql, params, (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row.count);
                }
            });
        });
    }
}

module.exports = Video;