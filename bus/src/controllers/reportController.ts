export const generateBookingReport = async (req: Request, res: Response) => {
    try {
        const { startDate, endDate } = req.query;
        const bookings = await Booking.find({
            createdAt: {
                $gte: new Date(startDate as string),
                $lte: new Date(endDate as string)
            }
        }).populate('bus user');

        res.json(bookings);
    } catch (error) {
        res.status(500).json({ message: 'Server error' });
    }
}; 