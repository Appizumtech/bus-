export const updatePaymentSettings = async (req: Request, res: Response) => {
    try {
        const settings = await Settings.findOneAndUpdate(
            {},
            { paymentGateways: req.body },
            { new: true, upsert: true }
        );
        res.json(settings);
    } catch (error) {
        res.status(400).json({ message: 'Error updating payment settings' });
    }
}; 