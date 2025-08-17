export const getAllBookings = async (req: Request, res: Response) => {
    try {
        const bookings = await Booking.find()
            .populate('bus')
            .populate('user');
        res.json(bookings);
    } catch (error) {
        res.status(500).json({ message: 'Server error' });
    }
};

export const updateBookingStatus = async (req: Request, res: Response) => {
    try {
        const booking = await Booking.findByIdAndUpdate(
            req.params.id,
            { status: req.body.status },
            { new: true }
        );
        res.json(booking);
    } catch (error) {
        res.status(400).json({ message: 'Error updating booking' });
    }
}; 