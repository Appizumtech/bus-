import { Request, Response } from 'express';
import Booking from '../models/booking';
import Bus from '../models/bus';

export const getDashboardStats = async (req: Request, res: Response) => {
    try {
        const totalBookings = await Booking.countDocuments();
        const totalRevenue = await Booking.aggregate([
            { $match: { status: 'confirmed' } },
            { $group: { _id: null, total: { $sum: '$amount' } } }
        ]);
        const activeBuses = await Bus.countDocuments({ status: 'active' });

        res.json({
            totalBookings,
            totalRevenue: totalRevenue[0]?.total || 0,
            activeBuses
        });
    } catch (error) {
        res.status(500).json({ message: 'Server error' });
    }
}; 